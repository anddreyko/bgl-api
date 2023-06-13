<?php

declare(strict_types=1);

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (Container $container) {
        $logger = new Logger('BGL-API');

        /**
         * @var array{
         *     level: Level,
         *     std: bool,
         *     file?: string
         * } $config
         */
        $config = $container->get('logger');
        $level = $config['level'];

        if ($config['std']) {
            $logger->pushHandler(new StreamHandler('php://stderr', $level));
        }

        if (!empty($config['file'])) {
            $logger->pushHandler(new StreamHandler($config['file'], $level));
        }

        return $logger;
    },

    'logger' => [
        'level' => env('APP_DEBUG') ? Level::Debug : Level::Info,
        'std' => true,
        'file' => null,
    ],
];
