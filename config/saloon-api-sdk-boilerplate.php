<?php

declare(strict_types=1);

return [
    'base_url' => env('SALOON_API_SDK_BASE_URL', 'https://api.example.com'),
    'token' => env('SALOON_API_SDK_TOKEN'),
    'auth_header' => env('SALOON_API_SDK_AUTH_HEADER', 'X-Api-Key'),
];
