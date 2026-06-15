<?php

namespace App\Services;

/**
 * Handles all episode-related API communication.
 */
class EpisodeService extends AbstractApiService
{
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
     * Get a single episode by ID.
     *
     * @param  int  $id
     * @return array The episode data, or an empty array if not found.
     */
    public function getEpisode(int $id): array
    {
        return $this->remember("episode.{$id}", fn () => $this->get("/episode/{$id}"));
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
}
