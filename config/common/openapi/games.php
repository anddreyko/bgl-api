<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Games\GetGame;
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
            '/v1/games/{id}' => [
                'get' => [
                    'summary' => 'Get game details',
                    'operationId' => 'getGame',
                    'tags' => ['Games'],
                    'x-message' => GetGame\Query::class,
                    'x-map' => ['id' => 'gameId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string', 'format' => 'uuid'],
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
                                                    'id' => ['type' => 'string', 'format' => 'uuid'],
                                                    'bgg_id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'year_published' => ['type' => 'integer', 'nullable' => true],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
