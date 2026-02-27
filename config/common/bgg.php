<?php

declare(strict_types=1);

return [
    'bgg' => [
        'base_url' => 'https://boardgamegeek.com',
        'search' => [
            'endpoint' => '/xmlapi2/search',
            'params' => ['type' => 'boardgame'],
            'timeout' => 10,
            'mapping' => [
                '@id' => 'bggId',
                'name@value' => 'name',
                'yearpublished@value' => 'yearPublished',
            ],
            'required' => ['bggId', 'name'],
        ],
    ],
];
