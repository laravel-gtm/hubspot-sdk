<?php

declare(strict_types=1);

return [
    'base_url' => env('HUBSPOT_BASE_URL', 'https://api.hubapi.com'),
    'api_key' => env('HUBSPOT_API_KEY'),
    'oauth' => [
        'user_model' => env('HUBSPOT_USER_MODEL', 'App\\Models\\User'),
        'token_column' => env('HUBSPOT_OAUTH_TOKEN_COLUMN', 'hubspot_access_token'),
    ],
    'rate_limit' => [
        'burst' => (int) env('HUBSPOT_RATE_LIMIT_BURST', 190),
        'daily' => (int) env('HUBSPOT_RATE_LIMIT_DAILY', 1000000),
    ],
];
