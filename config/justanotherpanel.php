<?php

return [
    'base_url' => env('JUSTANOTHERPANEL_BASE_URL', 'https://justanotherpanel.com'),
    'endpoint' => env('JUSTANOTHERPANEL_ENDPOINT', '/api/v2'),
    'api_key' => env('JUSTANOTHERPANEL_API_KEY'),
    'timeout' => (int) env('JUSTANOTHERPANEL_TIMEOUT', 30),
];
