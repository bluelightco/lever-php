<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lever API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key provided by Lever to access their API.
    |
    */

    'api_key' => env('LEVER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Lever API Base URL
    |--------------------------------------------------------------------------
    |
    | This value is the base URL for the Lever API.
    |
    */

    'base_url' => env('LEVER_BASE_URL', 'https://api.lever.co/v1/'),

    /*
    |--------------------------------------------------------------------------
    | Lever Rate Limit
    |--------------------------------------------------------------------------
    |
    | This value is the rate limit for the Lever API.
    |
    | rate_limit.cache_key: The cache key to store the rate limit data. Default: 'lever-rate-limiter'
    | rate_limit.max_cache_size: The maximum size of the cache in bytes. Default: 400000 bytes = 400 KB
    | rate_limit.cache_ttl: The time-to-live (TTL) for the cache in seconds. Default: 300 seconds = 5 minutes
    |
    */

    'rate_limit' => [
        'cache_key' => env('LEVER_RATE_LIMIT_CACHE_KEY', 'lever-rate-limiter'),
        'max_cache_size' => env('LEVER_RATE_LIMIT_MAX_CACHE_SIZE', 400000),
        'cache_ttl' => env('LEVER_RATE_LIMIT_CACHE_TTL', 300),
    ],
];
