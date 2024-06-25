<?php

namespace Bluelightco\LeverPhp\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

/**
 * @see \Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware
 */
class LeverRateStore implements Store
{
    public function get(): array
    {
        return Cache::get('lever-rate-limiter', []);
    }

    public function push(int $timestamp, int $limit)
    {
        Cache::put('lever-rate-limiter', array_merge($this->get(), [$timestamp]));
    }
}
