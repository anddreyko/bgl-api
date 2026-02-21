<?php

declare(strict_types=1);

use cebe\openapi\spec\OpenApi;
use Psr\Container\ContainerInterface;

return [
    OpenApi::class => static function (ContainerInterface $container) {
        /** @var array{openapi: string, info:string[], paths: string[]} $params */
        $params = $container->get('openapi');

        return new OpenApi($params);
    },

    'openapi' => [
        'openapi' => '1.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ],
];
