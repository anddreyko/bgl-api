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
                ],
                'get' => [
                    'summary' => 'List mates',
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
                ],
            ],
            '/v1/mates/{id}' => [
                'get' => [
                    'summary' => 'Get mate',
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
                ],
                'put' => [
                    'summary' => 'Update mate',
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
                ],
                'delete' => [
                    'summary' => 'Delete mate',
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
                ],
            ],
        ],
    ],
];
