<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Provides aggregate stats (total counts) across all API resources.
 */
class StatsService extends AbstractApiService
{
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
}
