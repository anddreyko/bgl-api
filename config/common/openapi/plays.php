<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\CloseSession;
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
                    'x-auth' => ['userId'],
                    'requestBody' => [
                        'required' => false,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '/v1/plays/sessions/{id}' => [
                'patch' => [
                    'summary' => 'Close play session',
                    'x-message' => CloseSession\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-map' => ['id' => 'sessionId'],
                    'x-auth' => ['userId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'requestBody' => [
                        'required' => false,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'finishedAt' => [
                                            'type' => 'string',
                                            'format' => 'date-time',
                                        ],
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
