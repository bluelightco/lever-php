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
        $data = [];
        $part = 1;

        // Retrieve and merge all parts
        while (Cache::has($this->getCacheKey($part))) { // Check if the part exists
            $partData = Cache::get($this->getCacheKey($part), []); // Retrieve the part data
            $data = array_merge($data, $partData); // Merge the part data with the main data
            $part++; // Move to the next part
        }

        return $data;
    }

    public function push(int $timestamp, int $limit)
    {
        // Retrieve current data
        $data = $this->get();
        $data[] = $timestamp;

        // Split data into parts and store each part separately
        $parts = $this->splitDataIntoParts($data);

        // Store each part in the cache with separate keys
        foreach ($parts as $index => $partData) {
            Cache::put($this->getCacheKey($index + 1), $partData, $this->cacheTtl);
        }

        // Remove any leftover parts that may have been left from previous larger dataset
        $this->cleanupExtraParts(count($parts) + 1);
    }

    private function splitDataIntoParts(array $data): array
    {
        $parts = [];
        $currentPart = [];

        foreach ($data as $item) {
            $currentPart[] = $item;
            if ($this->getCacheSize($currentPart) > $this->maxCacheSize) {
                array_pop($currentPart); // Remove the last item that caused the size to exceed
                $parts[] = $currentPart; // Store the current part in the parts array
                $currentPart = [$item]; // Start a new part with the last item that was removed
            }
        }

        if (! empty($currentPart)) {
            $parts[] = $currentPart; // Store the current part in the parts array
        }

        return $parts;
    }

    private function getCacheKey(int $part): string
    {
        return $this->cacheKey.':'.$part;
    }

    private function getCacheSize(array $data): int
    {
        return strlen(serialize($data));
    }

    private function cleanupExtraParts(int $startPart)
    {
        while (Cache::has($this->getCacheKey($startPart))) {
            Cache::forget($this->getCacheKey($startPart));
            $startPart++;
        }
    }
}
