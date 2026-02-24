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
                    'x-message' => GetUser\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-map' => ['id' => 'userId'],
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
