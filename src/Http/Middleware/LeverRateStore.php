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
        // Retrieve the entire data from cache
        $data = Cache::get($this->cacheKey, []);

        // Filter out any timestamps that are older than the cacheTtl
        $filteredData = array_filter($data, function ($timestamp) {
            return $timestamp >= now()->timestamp - $this->cacheTtl;
        });

        return $filteredData;
    }

    public function push(int $timestamp, int $limit)
    {
        // Retrieve the current filtered data
        $data = $this->get();

        // Add the new timestamp
        $data[] = $timestamp;

        // calculate the size of the data
        if ($this->getCacheSize($data) > $this->maxCacheSize) {
            // If the size exceeds the maxCacheSize, remove the oldest timestamp
            array_shift($data);
        }

        // Store the updated data back into the cache
        Cache::put($this->cacheKey, $data, $this->cacheTtl);
    }

    private function getCacheSize(array $data): int
    {
        return strlen(serialize($data));
    }
}
