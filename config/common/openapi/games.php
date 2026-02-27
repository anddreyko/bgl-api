<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Games\SearchGames;

return [
    'openapi' => [
        'paths' => [
            '/v1/games/search' => [
                'get' => [
                    'summary' => 'Search games',
                    'operationId' => 'searchGames',
                    'tags' => ['Games'],
                    'x-message' => SearchGames\Query::class,
                    'parameters' => [
                        [
                            'name' => 'q',
                            'in' => 'query',
                            'required' => true,
                            'schema' => ['type' => 'string', 'minLength' => 3],
                        ],
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
                                                        'items' => ['type' => 'object'],
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
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
