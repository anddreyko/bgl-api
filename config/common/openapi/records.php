<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/records/sessions' => [
                'post' => [
                    'summary' => 'Open play session (legacy)',
                    'operationId' => 'openSessionLegacy',
                    'tags' => ['Records (deprecated)'],
                    'deprecated' => true,
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
                                                    'color' => ['type' => 'string', 'maxLength' => 50, 'nullable' => true],
                                                    'team_tag' => ['type' => 'string', 'maxLength' => 50, 'nullable' => true],
                                                    'number' => ['type' => 'integer', 'minimum' => 0, 'nullable' => true],
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
            '/v1/records/sessions/{id}' => [
                'patch' => [
                    'summary' => 'Partially update play session (legacy)',
                    'operationId' => 'patchSessionLegacy',
                    'tags' => ['Records (deprecated)'],
                    'deprecated' => true,
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
                                                    'color' => ['type' => 'string', 'maxLength' => 50, 'nullable' => true],
                                                    'team_tag' => ['type' => 'string', 'maxLength' => 50, 'nullable' => true],
                                                    'number' => ['type' => 'integer', 'minimum' => 0, 'nullable' => true],
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
            ],
        ],
    ],
];
