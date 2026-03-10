<?php

declare(strict_types=1);

use Bgl\Application\Handlers\User\GetUser;
use Bgl\Application\Handlers\User\UpdateUser;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/user/{id}' => [
                'get' => [
                    'summary' => 'Get user info',
                    'operationId' => 'getUser',
                    'tags' => ['User'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => GetUser\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-map' => ['id' => 'userId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'User UUID or name',
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/User'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'patch' => [
                    'summary' => 'Update user profile',
                    'operationId' => 'updateUser',
                    'tags' => ['User'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => UpdateUser\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'User UUID',
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
                                        'name' => [
                                            'type' => 'string',
                                            'pattern' => '^[a-zA-Z0-9]+$',
                                            'minLength' => 1,
                                            'maxLength' => 255,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/User'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
