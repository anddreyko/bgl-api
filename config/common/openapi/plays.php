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
                    'operationId' => 'openSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
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
                    'responses' => [
                        '200' => [
                            'description' => 'Successful operation',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'code' => ['type' => 'integer', 'example' => 0],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'session_id' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/plays/sessions/{id}' => [
                'patch' => [
                    'summary' => 'Close play session',
                    'operationId' => 'closeSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
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
                    'responses' => [
                        '200' => [
                            'description' => 'Successful operation',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'code' => ['type' => 'integer', 'example' => 0],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'session_id' => ['type' => 'string'],
                                                    'started_at' => ['type' => 'string', 'format' => 'date-time'],
                                                    'finished_at' => ['type' => 'string', 'format' => 'date-time'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
