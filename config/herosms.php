<?php

return [
    'enabled' => filter_var(env('HEROSMS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => env('HEROSMS_BASE_URL', 'https://hero-sms.com/api/v1'),
    'api_key' => env('HEROSMS_API_KEY'),
    'timeout' => (int) env('HEROSMS_TIMEOUT', 30),
];
