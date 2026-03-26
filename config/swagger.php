<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Swagger API Documentation URL
    |--------------------------------------------------------------------------
    |
    | This URL is used to access the Swagger UI for your API documentation.
    |
    */
    'documentation_url' => env('APP_URL', 'http://localhost:8000') . '/api/duos',
    
    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL that will be used for all API endpoints in the
    | Swagger documentation.
    |
    */
    'api_base_url' => env('APP_URL', 'http://localhost:8000') . '/api/v1',
];
