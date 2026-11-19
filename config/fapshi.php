<?php

return [
    'enabled' => filter_var(env('FAPSHI_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => env('FAPSHI_BASE_URL', 'https://sandbox.fapshi.com'),
    'api_user' => env('FAPSHI_API_USER'),
    'api_key' => env('FAPSHI_API_KEY'),
    'webhook_secret' => env('FAPSHI_WEBHOOK_SECRET'),
    'timeout' => (int) env('FAPSHI_TIMEOUT', 20),
];
