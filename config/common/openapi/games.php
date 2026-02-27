<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Games\SearchGames;

return [
    'openapi' => [
        'paths' => [
            '/v1/games/search' => [
                'get' => [
                    'summary' => 'Search games',
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
                ],
            ],
        ],
    ],
];
