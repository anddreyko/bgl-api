<?php

declare(strict_types=1);

use App\Core\Tokens\Services\TokenizerService;
use Psr\Container\ContainerInterface;

return [
    TokenizerService::class => static function (ContainerInterface $container): TokenizerService {
        /** @var array{tokenize_timeout:string} $config */
        $config = $container->get('auth');

        return new TokenizerService(new DateInterval($config['tokenize_timeout']));
    },

    'auth' => [
        'tokenize_timeout' => 'PT1H',
    ],
];
