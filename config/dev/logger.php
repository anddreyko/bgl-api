<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

return [
    LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
        if ((bool)getenv('APP_DEBUG')) {
            /** @var array{file: string} $config */
            $config = $container->get('logger');

            $logger = new Logger('application');
            $logger->pushHandler(new StreamHandler($config['file']));
        } else {
            $logger = new NullLogger();
        }

        return $logger;
    },
];
