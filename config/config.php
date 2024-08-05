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
    | Lever User Id
    |--------------------------------------------------------------------------
    |
    | This value is the user id of the user to use for the Lever API.
    |
    */

    'user_email' => env('LEVER_USER_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Lever User Email
    |--------------------------------------------------------------------------
    |
    | This value is the email of the user to use for the Lever API.
    |
    */

    'user_email' => env('LEVER_USER_EMAIL', ''),

    /*
    |--------------------------------------------------------------------------
    | Lever Enabled
    |--------------------------------------------------------------------------
    |
    | This value is the flag to enable or disable the Lever API.
    |
    */

    'enabled' => env('LEVER_ENABLED', false),
];
