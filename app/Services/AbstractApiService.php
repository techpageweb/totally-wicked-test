<?php

namespace App\Services;

use App\Exceptions\ApiConnectionException;
use App\Exceptions\ApiRateLimitException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base class for all Rick and Morty API services.
 *
 * Provides shared HTTP transport, caching, and exception handling so that
 * individual resource services only need to implement their own domain methods.
 */
abstract class AbstractApiService
{
    /** @var string Base URL for all API requests. */
    protected string $baseUrl;

    /** @var int Cache TTL in seconds for individual API responses. */
    protected int $cacheTtl;

    /** @var int Cache TTL in seconds for derived filter option sets (longer than per-request cache). */
    protected int $filterCacheTtl;

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
     * Make a GET request to the API.
     *
     * Returns an empty array on 404 — both "item not found" and "no results for
     * these filters" are expressed as 404 by the Rick and Morty API.
     *
     * @param  string  $endpoint  Path relative to the base URL, e.g. '/character'.
     * @param  array   $params    Query parameters to append to the request.
     * @return array Decoded JSON response, or empty array on 404.
     * @throws ApiRateLimitException   When the API returns HTTP 429.
     * @throws ApiConnectionException  When the API is unreachable or returns a non-success, non-404 response.
     */
    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->status() === 429) {
                throw new ApiRateLimitException("Rate limit reached for {$endpoint}");
            }

            if ($response->status() === 404) {
                return [];
            }

            if ($response->failed()) {
                Log::warning("Rick and Morty API error: {$response->status()} for {$endpoint}");
                throw new ApiConnectionException("API returned {$response->status()} for {$endpoint}");
            }

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error("Rick and Morty API connection failed: {$e->getMessage()}");
            throw new ApiConnectionException("Could not connect to the Rick and Morty API: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Retrieve a cached value or store the result of the callback.
     * Empty arrays are never cached — they indicate no results and should be retried.
     *
     * @param  string    $key
     * @param  callable  $callback
     * @return array
     */
    protected function remember(string $key, callable $callback): array
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
