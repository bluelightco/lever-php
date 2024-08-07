<?php

namespace Bluelightco\LeverPhp\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

/**
 * @see \Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware
 */
class LeverRateStore implements Store
{
    private $cacheKey = 'lever-rate-limiter';

    private $maxCacheSize = 400000; // 400000 bytes = 400 KB

    private $cacheTtl = 300; // 300 seconds = 5 minutes

    public function __construct()
    {
        $this->cacheKey = config('lever-php.rate_limit.cache_key');
        $this->maxCacheSize = config('lever-php.rate_limit.max_cache_size');
        $this->cacheTtl = config('lever-php.rate_limit.cache_ttl');
    }

    public function get(): array
    {
        $data = Cache::get($this->cacheKey, []);

        // Check the size of the data in the cache
        if ($this->getCacheSize($data) > $this->maxCacheSize) {
            // If the data is too large, clear the cache
            Cache::forget($this->cacheKey);

            return [];
        }

        return $data;
    }

    public function push(int $timestamp, int $limit)
    {
        $data = array_merge($this->get(), [$timestamp]);

        // Check the size of the data before saving it
        if ($this->getCacheSize($data) <= $this->maxCacheSize) {
            Cache::put($this->cacheKey, $data, $this->cacheTtl);
        } else {
            // If the data is too large, clear the cache and save only the new data
            Cache::forget($this->cacheKey);
            Cache::put($this->cacheKey, [$timestamp], $this->cacheTtl);
        }
    }

    private function getCacheSize(array $data): int
    {
        return strlen(serialize($data));
    }
}
