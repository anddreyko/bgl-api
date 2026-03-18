<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\DeletePlay;
use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Application\Handlers\Plays\GetPlay;
use Bgl\Application\Handlers\Plays\ListPlays;
use Bgl\Application\Handlers\Plays\RestorePlay;
use Bgl\Application\Handlers\Plays\UpdatePlay;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;
use Bgl\Presentation\Api\Interceptors\OptionalAuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/plays' => [
                'get' => [
                    'summary' => 'List user play sessions',
                    'operationId' => 'listSessions',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []], []],
                    'x-message' => ListPlays\Query::class,
                    'x-interceptors' => [OptionalAuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['author_id' => 'authorId', 'game_id' => 'gameId'],
                    'parameters' => [
                        [
                            'name' => 'author_id',
                            'in' => 'query',
                            'required' => false,
                            'description' => 'User UUID or name',
                            'schema' => ['type' => 'string'],
                        ],
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
                        [
                            'name' => 'status',
                            'in' => 'query',
                            'required' => false,
                            'description' => 'Filter by lifecycle status',
                            'schema' => ['type' => 'string', 'enum' => ['current', 'finished']],
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
                                                        'items' => ['$ref' => '#/components/schemas/Play'],
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
                    'x-map' => [
                        'game_id' => 'gameId',
                        'location_id' => 'locationId',
                        'started_at' => 'startedAt',
                        'finished_at' => 'finishedAt',
                    ],
                    'requestBody' => [
                        'required' => false,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'game_id' => ['type' => 'string', 'format' => 'uuid'],
                                        'location_id' => ['type' => 'string', 'format' => 'uuid', 'nullable' => true],
                                        'name' => ['type' => 'string', 'maxLength' => 255],
                                        'notes' => ['type' => 'string', 'nullable' => true],
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
                                                    'color' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'team_tag' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'number' => [
                                                        'type' => 'integer',
                                                        'minimum' => 0,
                                                        'nullable' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => ['$ref' => '#/components/responses/Play'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/plays/{id}' => [
                'get' => [
                    'summary' => 'View play session details',
                    'operationId' => 'getSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []], []],
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
                        '200' => ['$ref' => '#/components/responses/Play'],
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
                    'x-map' => [
                        'id' => 'sessionId',
                        'game_id' => 'gameId',
                        'location_id' => 'locationId',
                    ],
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
                                        'location_id' => ['type' => 'string', 'format' => 'uuid', 'nullable' => true],
                                        'visibility' => [
                                            'type' => 'string',
                                            'enum' => ['private', 'link', 'participants', 'authenticated', 'public'],
                                            'default' => 'private',
                                        ],
                                        'notes' => ['type' => 'string', 'nullable' => true],
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
                                                    'color' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'team_tag' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'number' => [
                                                        'type' => 'integer',
                                                        'minimum' => 0,
                                                        'nullable' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Play'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'patch' => [
                    'summary' => 'Partially update play session',
                    'description' => 'Updates only provided fields. If finished_at is provided, triggers finalization.',
                    'operationId' => 'patchSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => FinalizePlay\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-map' => [
                        'id' => 'sessionId',
                        'game_id' => 'gameId',
                        'location_id' => 'locationId',
                        'finished_at' => 'finishedAt',
                    ],
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
                                        'location_id' => ['type' => 'string', 'format' => 'uuid', 'nullable' => true],
                                        'visibility' => [
                                            'type' => 'string',
                                            'enum' => ['private', 'link', 'participants', 'authenticated', 'public'],
                                        ],
                                        'notes' => ['type' => 'string', 'nullable' => true],
                                        'finished_at' => ['type' => 'string', 'format' => 'date-time'],
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
                                                    'color' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'team_tag' => [
                                                        'type' => 'string',
                                                        'maxLength' => 50,
                                                        'nullable' => true,
                                                    ],
                                                    'number' => [
                                                        'type' => 'integer',
                                                        'minimum' => 0,
                                                        'nullable' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Play'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'delete' => [
                    'summary' => 'Delete play session',
                    'operationId' => 'deleteSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => DeletePlay\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'sessionId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '204' => ['description' => 'Play deleted'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/plays/{id}/restore' => [
                'patch' => [
                    'summary' => 'Restore deleted play session',
                    'operationId' => 'restoreSession',
                    'tags' => ['Plays'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => RestorePlay\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'sessionId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Play'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
