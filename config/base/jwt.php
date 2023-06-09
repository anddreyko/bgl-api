<?php

declare(strict_types=1);

use Firebase\JWT\Key;
use Psr\Container\ContainerInterface;

return [
    Key::class => static function (ContainerInterface $container) {
        /** @var array{key: string, algo: string} $config */
        $config = $container->get('jwt');

        return new Key($config['key'], $config['algo']);
    },

    'jwt' => [
        'key' => getenv('JWT_KEY'),
        'algo' => 'HS512',
    ],
];
