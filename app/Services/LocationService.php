<?php

namespace App\Services;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Handles all location-related API communication.
 */
class LocationService extends AbstractApiService
{
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
     * @param  int  $id
     * @return array The location data, or an empty array if not found.
     */
    public function getLocation(int $id): array
    {
        return $this->remember("location.{$id}", fn () => $this->get("/location/{$id}"));
    }

    /**
     * Derive the unique filter values (type, dimension) from all location pages.
     *
     * Uses a 24-hour cache TTL. Catches API errors mid-scan and caches
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
        $page    = 1;

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
        } catch (ApiRateLimitException|ApiConnectionException) {
            Log::warning('Rick and Morty API unavailable while building location filter options.');
        }

        foreach ($options as &$values) {
            sort($values);
        }

        if (! empty(array_filter($options))) {
            Cache::put('locations.filter_options', $options, $this->filterCacheTtl);
        }

        return $options;
    }
}
