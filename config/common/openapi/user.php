<?php

declare(strict_types=1);

use Bgl\Application\Handlers\User\GetUser;
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
            ],
        ],
    ],
];
