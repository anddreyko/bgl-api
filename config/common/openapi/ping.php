<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Ping\Command;

return [
    'openapi' => [
        'paths' => [
            '/ping' => [
                'get' => [
                    'summary' => 'Health check',
                    'x-message' => Command::class,
                ],
            ],
        ],
    ],
];
