<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Application\Handlers\Plays\GetPlay;
use Bgl\Application\Handlers\Plays\ListPlays;
use Bgl\Application\Handlers\Plays\UpdatePlay;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;
use Bgl\Presentation\Api\Interceptors\OptionalAuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/plays/sessions' => [
                'get' => [
                    'summary' => 'List user play sessions',
                    'operationId' => 'listSessions',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => ListPlays\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'integer', 'default' => 1, 'minimum' => 1],
                        ],
                        [
                            'name' => 'size',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'integer', 'default' => 20, 'minimum' => 1, 'maximum' => 100],
                        ],
                        [
                            'name' => 'game_id',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
                        ],
                        [
                            'name' => 'from',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                        [
                            'name' => 'to',
                            'in' => 'query',
                            'required' => false,
                            'schema' => ['type' => 'string', 'format' => 'date-time'],
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
                                                    'items' => [
                                                        'type' => 'array',
                                                        'items' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id' => ['type' => 'string'],
                                                                'name' => ['type' => 'string', 'nullable' => true],
                                                                'status' => ['type' => 'string'],
                                                                'visibility' => ['type' => 'string'],
                                                                'started_at' => [
                                                                    'type' => 'string',
                                                                    'format' => 'date-time',
                                                                ],
                                                                'finished_at' => [
                                                                    'type' => 'string',
                                                                    'format' => 'date-time',
                                                                    'nullable' => true,
                                                                ],
                                                                'game' => [
                                                                    'type' => 'object',
                                                                    'nullable' => true,
                                                                    'properties' => [
                                                                        'id' => ['type' => 'string'],
                                                                        'name' => ['type' => 'string'],
                                                                    ],
                                                                ],
                                                                'players' => [
                                                                    'type' => 'array',
                                                                    'items' => [
                                                                        'type' => 'object',
                                                                        'properties' => [
                                                                            'id' => ['type' => 'string'],
                                                                            'mate_id' => ['type' => 'string'],
                                                                            'score' => [
                                                                                'type' => 'integer',
                                                                                'nullable' => true,
                                                                            ],
                                                                            'is_winner' => ['type' => 'boolean'],
                                                                            'color' => [
                                                                                'type' => 'string',
                                                                                'nullable' => true,
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                    'total' => ['type' => 'integer'],
                                                    'page' => ['type' => 'integer'],
                                                    'size' => ['type' => 'integer'],
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
                                            'enum' => ['private', 'link', 'participants', 'authenticated', 'public'],
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
                'get' => [
                    'summary' => 'View play session details',
                    'operationId' => 'getSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => GetPlay\Query::class,
                    'x-interceptors' => [OptionalAuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'playId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
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
                                                    'id' => ['type' => 'string'],
                                                    'name' => ['type' => 'string', 'nullable' => true],
                                                    'status' => ['type' => 'string'],
                                                    'visibility' => ['type' => 'string'],
                                                    'started_at' => ['type' => 'string', 'format' => 'date-time'],
                                                    'finished_at' => [
                                                        'type' => 'string',
                                                        'format' => 'date-time',
                                                        'nullable' => true,
                                                    ],
                                                    'game' => [
                                                        'type' => 'object',
                                                        'nullable' => true,
                                                        'properties' => [
                                                            'id' => ['type' => 'string'],
                                                            'name' => ['type' => 'string'],
                                                        ],
                                                    ],
                                                    'players' => [
                                                        'type' => 'array',
                                                        'items' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id' => ['type' => 'string'],
                                                                'mate_id' => ['type' => 'string'],
                                                                'score' => ['type' => 'integer', 'nullable' => true],
                                                                'is_winner' => ['type' => 'boolean'],
                                                                'color' => ['type' => 'string', 'nullable' => true],
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
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'put' => [
                    'summary' => 'Update play session (full replace)',
                    'description' => 'Replaces all mutable fields. ' .
                        'Omitted fields will be set to their defaults (name=null, game_id=null, visibility=private).',
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
                                            'enum' => ['private', 'link', 'participants', 'authenticated', 'public'],
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
                                        'finished_at' => [
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
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
