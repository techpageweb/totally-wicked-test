<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RickAndMortyService
{
    private string $baseUrl;

    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('rickandmorty.base_url');
        $this->cacheTtl = config('rickandmorty.cache_ttl');
    }

    public function getCharacters(array $params = []): array
    {
        $cacheKey = 'characters.' . md5(serialize($params));

        return $this->remember($cacheKey, fn () => $this->get('/character', $params));
    }

    public function getCharacter(int $id): array
    {
        return $this->remember("character.{$id}", fn () => $this->get("/character/{$id}"));
    }

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

    private function remember(string $key, callable $callback): array
    {
        return Cache::remember($key, $this->cacheTtl, $callback);
    }
}
