<?php

declare(strict_types=1);

$successEnvelope = static fn(array $dataSchema): array => [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 0],
        'data' => $dataSchema,
    ],
];

$successResponse = static fn(string $description, array $dataSchema): array => [
    'description' => $description,
    'content' => [
        'application/json' => [
            'schema' => $successEnvelope($dataSchema),
        ],
    ],
];

return [
    'openapi' => [
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'BoardGameLog API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'securitySchemes' => [
                'BearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
            'schemas' => [
                'ErrorResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'code' => ['type' => 'integer', 'example' => 1],
                        'message' => ['type' => 'string'],
                    ],
                ],
                'TokenPair' => [
                    'type' => 'object',
                    'description' => 'Both tokens are JWT (HS256). ' .
                        'Common claims: `sub` (user ID), `type` ("access"|"refresh"), ' .
                        '`tokenVersion` (int), `iat`, `exp`. ' .
                        'Access token extra claims: `name` (string), `email` (string).',
                    'properties' => [
                        'access_token' => [
                            'type' => 'string',
                            'description' => 'Short-lived JWT with name/email claims for SSR.',
                        ],
                        'refresh_token' => [
                            'type' => 'string',
                            'description' => 'Long-lived JWT for obtaining a new token pair via POST /v1/auth/refresh.',
                        ],
                    ],
                ],
                'Mate' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                        'notes' => ['type' => 'string', 'nullable' => true],
                        'is_system' => ['type' => 'boolean'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Location' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                        'address' => ['type' => 'string', 'nullable' => true],
                        'notes' => ['type' => 'string', 'nullable' => true],
                        'url' => ['type' => 'string', 'nullable' => true],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'email' => ['type' => 'string', 'format' => 'email'],
                        'name' => ['type' => 'string'],
                        'is_active' => ['type' => 'boolean'],
                        'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'PasskeyOptions' => [
                    'type' => 'object',
                    'properties' => [
                        'options' => ['type' => 'object'],
                    ],
                ],
                'Player' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'mate' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'string'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                        'score' => ['type' => 'integer', 'nullable' => true],
                        'is_winner' => ['type' => 'boolean'],
                        'color' => ['type' => 'string', 'nullable' => true],
                        'team_tag' => ['type' => 'string', 'maxLength' => 50, 'nullable' => true],
                        'number' => ['type' => 'integer', 'minimum' => 0, 'nullable' => true],
                    ],
                ],
                'Play' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'author' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'string'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                        'name' => ['type' => 'string', 'nullable' => true],
                        'visibility' => ['type' => 'string'],
                        'started_at' => ['type' => 'string', 'format' => 'date-time'],
                        'finished_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
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
                            'items' => ['$ref' => '#/components/schemas/Player'],
                        ],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['current', 'finished'],
                            'description' => 'Read-only lifecycle status',
                        ],
                        'notes' => ['type' => 'string', 'nullable' => true],
                        'location' => [
                            'type' => 'object',
                            'nullable' => true,
                            'properties' => [
                                'id' => ['type' => 'string'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
                'Game' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string', 'format' => 'uuid'],
                        'bgg_id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'year_published' => ['type' => 'integer', 'nullable' => true],
                    ],
                ],
            ],
            'responses' => [
                'BadRequest' => [
                    'description' => 'Bad request',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                'Unauthorized' => [
                    'description' => 'Unauthorized',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                'ValidationError' => [
                    'description' => 'Validation error',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                'NotFound' => [
                    'description' => 'Resource not found',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                'InternalError' => [
                    'description' => 'Internal server error',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                'String' => $successResponse('Successful operation', ['type' => 'string']),
                'Null' => $successResponse('Successful operation', ['type' => 'string', 'nullable' => true]),
                'TokenPair' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/TokenPair']
                ),
                'Mate' => $successResponse('Successful operation', ['$ref' => '#/components/schemas/Mate']),
                'Location' => $successResponse('Successful operation', ['$ref' => '#/components/schemas/Location']),
                'User' => $successResponse('Successful operation', ['$ref' => '#/components/schemas/User']),
                'PasskeyOptions' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/PasskeyOptions']
                ),
                'Play' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/Play']
                ),
                'Game' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/Game']
                ),
            ],
        ],
    ],
];
