<?php

namespace App\Services;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Handles all character-related API communication.
 */
class CharacterService extends AbstractApiService
{
    /**
     * Get a paginated list of characters.
     *
     * @param  array  $params  Supported filters: page, name, status, species, type, gender
     * @return array{info: array, results: array}|array
     */
    public function getCharacters(array $params = []): array
    {
        $cacheKey = 'characters.' . md5(serialize($params));

        return $this->remember($cacheKey, fn () => $this->get('/character', $params));
    }

    /**
     * Get a single character by ID.
     *
     * @param  int  $id
     * @return array The character data, or an empty array if not found.
     */
    public function getCharacter(int $id): array
    {
        return $this->remember("character.{$id}", fn () => $this->get("/character/{$id}"));
    }

    /**
     * Get multiple characters by their IDs in a single request.
     *
     * The API returns a single object when one ID is given and an array for multiple,
     * so the result is always normalised to an array of characters.
     *
     * @param  int[]  $ids
     * @return array<int, array>
     */
    public function getMultipleCharacters(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $cacheKey = 'characters.multi.' . md5(implode(',', $ids));

        return $this->remember($cacheKey, function () use ($ids) {
            $result = $this->get('/character/' . implode(',', $ids));

            return isset($result['id']) ? [$result] : $result;
        });
    }

    /**
     * Derive the unique filter values (status, gender, species) from all character pages.
     *
     * Uses a 24-hour cache TTL to avoid repeating the full page scan on every hour boundary.
     * Catches API errors mid-scan and caches whatever was collected so far.
     *
     * @return array{status: string[], gender: string[], species: string[]}
     */
    public function getCharacterFilterOptions(): array
    {
        $cached = Cache::get('characters.filter_options');
        if ($cached !== null) {
            return $cached;
        }

        $options = ['status' => [], 'gender' => [], 'species' => []];
        $page    = 1;

        try {
            do {
                $data = $this->getCharacters(['page' => $page]);

                foreach ($data['results'] ?? [] as $character) {
                    foreach (array_keys($options) as $field) {
                        $value = $character[$field] ?? '';
                        if ($value !== '' && ! in_array($value, $options[$field], true)) {
                            $options[$field][] = $value;
                        }
                    }
                }

                $totalPages = $data['info']['pages'] ?? 1;
                $page++;
            } while ($page <= $totalPages);
        } catch (ApiRateLimitException|ApiConnectionException) {
            Log::warning('Rick and Morty API unavailable while building character filter options.');
        }

        foreach ($options as &$values) {
            sort($values);
        }

        if (! empty(array_filter($options))) {
            Cache::put('characters.filter_options', $options, $this->filterCacheTtl);
        }

        return $options;
    }
}
