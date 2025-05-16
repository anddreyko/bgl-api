<?php

declare(strict_types=1);

use App\Infrastructure\Tokens\Tokenizer;
use Psr\Container\ContainerInterface;

return [
    Tokenizer::class => static function (ContainerInterface $container): Tokenizer {
        /** @var array{tokenize_timeout:string} $config */
        $config = $container->get('auth');

        return new Tokenizer(new DateInterval($config['tokenize_timeout']));
    },

    'auth' => [
        'tokenize_timeout' => 'PT1H',
    ],
];
