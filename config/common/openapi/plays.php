<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\UpdatePlay;
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
                    'x-message' => CreatePlay\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'requestBody' => [
                        'required' => false,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'game_id' => ['type' => 'string', 'format' => 'uuid'],
                                        'name' => ['type' => 'string', 'maxLength' => 255],
                                        'started_at' => ['type' => 'string', 'format' => 'date-time'],
                                        'finished_at' => ['type' => 'string', 'format' => 'date-time'],
                                        'visibility' => [
                                            'type' => 'string',
                                            'enum' => ['private', 'link', 'friends', 'registered', 'public'],
                                            'default' => 'private',
                                        ],
                                        'players' => [
                                            'type' => 'array',
                                            'minItems' => 1,
                                            'items' => [
                                                'type' => 'object',
                                                'required' => ['mate_id'],
                                                'properties' => [
                                                    'mate_id' => ['type' => 'string', 'format' => 'uuid'],
                                                    'score' => ['type' => 'integer', 'nullable' => true],
                                                    'is_winner' => ['type' => 'boolean', 'default' => false],
                                                    'color' => ['type' => 'string', 'maxLength' => 50],
                                                ],
                                            ],
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
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/plays/sessions/{id}' => [
                'put' => [
                    'summary' => 'Update play session',
                    'operationId' => 'updateSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => UpdatePlay\Command::class,
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
                                        'name' => ['type' => 'string', 'maxLength' => 255, 'nullable' => true],
                                        'game_id' => ['type' => 'string', 'format' => 'uuid', 'nullable' => true],
                                        'visibility' => [
                                            'type' => 'string',
                                            'enum' => ['private', 'link', 'friends', 'registered', 'public'],
                                            'default' => 'private',
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
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'patch' => [
                    'summary' => 'Close play session',
                    'operationId' => 'closeSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => FinalizePlay\Command::class,
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
