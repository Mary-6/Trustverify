<?php

return [
    'base_url' => env('FAUSSMS_BASE_URL', 'https://faussms.com'),
    'api_key' => env('FAUSSMS_API_KEY'),
    'timeout' => (int) env('FAUSSMS_TIMEOUT', 20),
    'endpoints' => [
        // Endpoint paths must be filled from authorized FAUSSMS API documentation.
        'place_order' => env('FAUSSMS_ENDPOINT_PLACE_ORDER'),
        'order_status' => env('FAUSSMS_ENDPOINT_ORDER_STATUS'),
        'catalog' => env('FAUSSMS_ENDPOINT_CATALOG'),
        'number_purchase' => env('FAUSSMS_ENDPOINT_NUMBER_PURCHASE'),
        'number_messages' => env('FAUSSMS_ENDPOINT_NUMBER_MESSAGES'),
    ],
];
