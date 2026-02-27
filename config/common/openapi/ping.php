<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Ping\Command;

return [
    'openapi' => [
        'paths' => [
            '/ping' => [
                'get' => [
                    'summary' => 'Health check',
                    'operationId' => 'ping',
                    'tags' => ['System'],
                    'x-message' => Command::class,
                    'responses' => [
                        '200' => [
                            'description' => 'Successful operation',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'code' => ['type' => 'integer', 'example' => 0],
                                            'data' => ['type' => 'object'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
