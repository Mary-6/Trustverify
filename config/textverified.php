<?php

return [
    'enabled' => filter_var(env('TEXTVERIFIED_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => env('TEXTVERIFIED_BASE_URL', 'https://www.textverified.com'),
    'api_key' => env('TEXTVERIFIED_API_KEY'),
    'api_username' => env('TEXTVERIFIED_API_USERNAME'),
    'timeout' => (int) env('TEXTVERIFIED_TIMEOUT', 30),
];
