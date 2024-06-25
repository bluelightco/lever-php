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
];
