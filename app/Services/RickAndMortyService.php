<?php

namespace App\Services;

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
    private string $baseUrl;

    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('rickandmorty.base_url');
        $this->cacheTtl = config('rickandmorty.cache_ttl');
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
     * Get a single character by ID.
     *
     * @return array The character data, or an empty array if not found.
     */
    public function getCharacter(int $id): array
    {
        return $this->remember("character.{$id}", fn () => $this->get("/character/{$id}"));
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
     * Make a GET request to the API.
     *
     * @return array Decoded JSON response, or empty array on failure.
     */
    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl . $endpoint, $params);

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
     */
    private function remember(string $key, callable $callback): array
    {
        return Cache::remember($key, $this->cacheTtl, $callback);
    }
}
