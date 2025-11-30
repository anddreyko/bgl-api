<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
        /** @var array{file: string} $config */
        $config = $container->get('logger');

        $logger = new Logger('application');
        $logger->pushHandler(new StreamHandler($config['file']));

        return $logger;
    },

    'logger' => [
        'file' => __DIR__ . '/../../var/.logs/application.log',
    ],
];
