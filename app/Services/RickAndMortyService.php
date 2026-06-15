<?php

namespace App\Services;

use App\Exceptions\ApiRateLimitException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Handles all communication with the Rick and Morty REST API.
 *
 * All responses are cached to reduce API calls and guard against rate limiting.
 * Returns an empty array on any failure so callers don't need to handle exceptions.
 */
class RickAndMortyService
{
    /** @var string Base URL for all API requests. */
    private string $baseUrl;

    /** @var int Cache TTL in seconds for individual API responses. */
    private int $cacheTtl;

    /** @var int Cache TTL in seconds for derived filter option sets (longer than per-request cache). */
    private int $filterCacheTtl;

    /**
     * Reads API base URL and cache TTL values from application config.
     */
    public function __construct()
    {
        $this->baseUrl        = config('rickandmorty.base_url');
        $this->cacheTtl       = config('rickandmorty.cache_ttl');
        $this->filterCacheTtl = config('rickandmorty.filter_cache_ttl');
    }

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
     * Derive the unique filter values (status, gender, species) from all character pages.
     *
     * Uses a 24-hour cache TTL to avoid repeating the full page scan on every hour boundary.
     * Catches rate limit errors mid-scan and caches whatever was collected so far.
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
        $page = 1;

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
        } catch (ApiRateLimitException) {
            Log::warning('Rick and Morty API rate limit hit while building character filter options.');
        }

        foreach ($options as &$values) {
            sort($values);
        }

        if (! empty(array_filter($options))) {
            Cache::put('characters.filter_options', $options, $this->filterCacheTtl);
        }

        return $options;
    }

    /**
     * Get a single character by ID.
     *
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
     * Get the total counts for characters, episodes, and locations.
     *
     * Fetches page 1 of each resource and reads the info.count field.
     * Cached for 24 hours as these numbers change infrequently.
     *
     * @return array{characters: int|null, episodes: int|null, locations: int|null}
     */
    public function getStats(): array
    {
        $cached = Cache::get('stats.counts');
        if ($cached !== null) {
            return $cached;
        }

        $stats = [
            'characters' => $this->get('/character', ['page' => 1])['info']['count'] ?? null,
            'episodes'   => $this->get('/episode',   ['page' => 1])['info']['count'] ?? null,
            'locations'  => $this->get('/location',  ['page' => 1])['info']['count'] ?? null,
        ];

        if (array_filter($stats, fn ($v) => $v !== null)) {
            Cache::put('stats.counts', $stats, $this->filterCacheTtl);
        }

        return $stats;
    }

    /**
     * Get a paginated list of episodes.
     *
     * @param  array  $params  Supported filters: page, name, episode
     * @return array{info: array, results: array}|array
     */
    public function getEpisodes(array $params = []): array
    {
        $cacheKey = 'episodes.' . md5(serialize($params));

        return $this->remember($cacheKey, fn () => $this->get('/episode', $params));
    }

    /**
     * Get multiple episodes by their IDs in a single request.
     *
     * The API returns a single object when one ID is given and an array for multiple,
     * so the result is always normalised to an array of episodes.
     *
     * @param  int[]  $ids
     * @return array<int, array>
     */
    public function getMultipleEpisodes(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $cacheKey = 'episodes.multi.' . md5(implode(',', $ids));

        return $this->remember($cacheKey, function () use ($ids) {
            $result = $this->get('/episode/' . implode(',', $ids));

            return isset($result['id']) ? [$result] : $result;
        });
    }

    /**
     * Get a single episode by ID.
     *
     * @return array The episode data, or an empty array if not found.
     */
    public function getEpisode(int $id): array
    {
        return $this->remember("episode.{$id}", fn () => $this->get("/episode/{$id}"));
    }

    /**
     * Get a paginated list of locations.
     *
     * @param  array  $params  Supported filters: page, name, type, dimension
     * @return array{info: array, results: array}|array
     */
    public function getLocations(array $params = []): array
    {
        $cacheKey = 'locations.' . md5(serialize($params));

        return $this->remember($cacheKey, fn () => $this->get('/location', $params));
    }

    /**
     * Get a single location by ID.
     *
     * @return array The location data, or an empty array if not found.
     */
    public function getLocation(int $id): array
    {
        return $this->remember("location.{$id}", fn () => $this->get("/location/{$id}"));
    }

    /**
     * Derive the unique filter values (type, dimension) from all location pages.
     *
     * Uses a 24-hour cache TTL. Catches rate limit errors mid-scan and caches
     * whatever was collected so far.
     *
     * @return array{type: string[], dimension: string[]}
     */
    public function getLocationFilterOptions(): array
    {
        $cached = Cache::get('locations.filter_options');
        if ($cached !== null) {
            return $cached;
        }

        $options = ['type' => [], 'dimension' => []];
        $page = 1;

        try {
            do {
                $data = $this->getLocations(['page' => $page]);

                foreach ($data['results'] ?? [] as $location) {
                    foreach (array_keys($options) as $field) {
                        $value = $location[$field] ?? '';
                        if ($value !== '' && $value !== 'unknown' && ! in_array($value, $options[$field], true)) {
                            $options[$field][] = $value;
                        }
                    }
                }

                $totalPages = $data['info']['pages'] ?? 1;
                $page++;
            } while ($page <= $totalPages);
        } catch (ApiRateLimitException) {
            Log::warning('Rick and Morty API rate limit hit while building location filter options.');
        }

        foreach ($options as &$values) {
            sort($values);
        }

        if (! empty(array_filter($options))) {
            Cache::put('locations.filter_options', $options, $this->filterCacheTtl);
        }

        return $options;
    }

    /**
     * Make a GET request to the API.
     *
     * @param  string  $endpoint  Path relative to the base URL, e.g. '/character'.
     * @param  array   $params    Query parameters to append to the request.
     * @return array Decoded JSON response, or empty array on any non-429 failure.
     * @throws ApiRateLimitException  When the API returns HTTP 429.
     */
    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->status() === 429) {
                throw new ApiRateLimitException("Rate limit reached for {$endpoint}");
            }

            if ($response->failed()) {
                Log::warning("Rick and Morty API error: {$response->status()} for {$endpoint}");

                return [];
            }

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error("Rick and Morty API connection failed: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Retrieve a cached value or store the result of the callback.
     * Empty arrays are never cached — they indicate a failed request and should be retried.
     */
    private function remember(string $key, callable $callback): array
    {
        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        $result = $callback();

        if (! empty($result)) {
            Cache::put($key, $result, $this->cacheTtl);
        }

        return $result;
    }
}
