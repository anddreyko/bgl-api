<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Mates\CreateMate;
use Bgl\Application\Handlers\Mates\DeleteMate;
use Bgl\Application\Handlers\Mates\GetMate;
use Bgl\Application\Handlers\Mates\ListMates;
use Bgl\Application\Handlers\Mates\UpdateMate;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/mates' => [
                'post' => [
                    'summary' => 'Create mate',
                    'operationId' => 'createMate',
                    'tags' => ['Mates'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => CreateMate\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['name'],
                                    'properties' => [
                                        'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
                                        'notes' => ['type' => 'string', 'maxLength' => 500],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => ['$ref' => '#/components/responses/Mate'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'get' => [
                    'summary' => 'List mates',
                    'operationId' => 'listMates',
                    'tags' => ['Mates'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => ListMates\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
                        ],
                        [
                            'name' => 'size',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20],
                        ],
                        [
                            'name' => 'sort',
                            'in' => 'query',
                            'schema' => ['type' => 'string', 'enum' => ['name', 'createdAt'], 'default' => 'name'],
                        ],
                        [
                            'name' => 'order',
                            'in' => 'query',
                            'schema' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'default' => 'asc'],
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
                                                        'items' => ['$ref' => '#/components/schemas/Mate'],
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
            ],
            '/v1/mates/{id}' => [
                'get' => [
                    'summary' => 'Get mate',
                    'operationId' => 'getMate',
                    'tags' => ['Mates'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => GetMate\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'mateId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Mate'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'put' => [
                    'summary' => 'Update mate',
                    'operationId' => 'updateMate',
                    'tags' => ['Mates'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => UpdateMate\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'mateId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['name'],
                                    'properties' => [
                                        'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
                                        'notes' => ['type' => 'string', 'maxLength' => 500],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Mate'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'delete' => [
                    'summary' => 'Delete mate',
                    'operationId' => 'deleteMate',
                    'tags' => ['Mates'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => DeleteMate\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'mateId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '204' => ['description' => 'Mate deleted'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
