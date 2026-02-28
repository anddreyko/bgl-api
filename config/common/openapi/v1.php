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
                    'properties' => [
                        'access_token' => ['type' => 'string'],
                        'refresh_token' => ['type' => 'string'],
                        'expires_in' => ['type' => 'integer'],
                    ],
                ],
                'Mate' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                        'notes' => ['type' => 'string', 'nullable' => true],
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
                'StringSuccess' => $successResponse('Successful operation', ['type' => 'string']),
                'NullSuccess' => $successResponse('Successful operation', ['type' => 'string', 'nullable' => true]),
                'TokenPairSuccess' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/TokenPair']
                ),
                'MateSuccess' => $successResponse('Successful operation', ['$ref' => '#/components/schemas/Mate']),
                'UserSuccess' => $successResponse('Successful operation', ['$ref' => '#/components/schemas/User']),
                'PasskeyOptionsSuccess' => $successResponse(
                    'Successful operation',
                    ['$ref' => '#/components/schemas/PasskeyOptions']
                ),
            ],
        ],
    ],
];
