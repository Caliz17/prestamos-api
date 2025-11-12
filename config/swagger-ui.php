<?php

use Wotz\SwaggerUi\Http\Middleware\EnsureUserIsAuthorized;

return [
    'files' => [
        [
            'path' => 'swagger',
            'title' => env('APP_NAME', 'Laravel') . ' - Swagger',
            'versions' => [
                'v1' => resource_path('swagger/openapi.json'),
            ],
            'default' => 'v1',
            'middleware' => ['web'], // 👈 Asegúrate de que esté así
            'validator_url' => false,
            'modify_file' => true,
            'server_url' => env('APP_URL', 'http://localhost:8000'),
        ],
    ],
];
