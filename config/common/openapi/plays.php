<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\OpenSession;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/plays/sessions' => [
                'post' => [
                    'summary' => 'Open play session',
                    'x-message' => OpenSession\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'requestBody' => [
                        'required' => false,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'userId' => [
                                            'x-target' => 'userId',
                                            'x-source' => 'attribute:auth.userId',
                                        ],
                                        'name' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
