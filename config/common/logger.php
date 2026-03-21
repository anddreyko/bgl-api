<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sentry\Monolog\Handler as SentryHandler;

return [
    LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
        /** @var array{file: string} $config */
        $config = $container->get('logger');

        $logger = new Logger('application');
        $logger->pushHandler(new StreamHandler($config['file']));

        /** @var bool $sentryInitialized */
        $sentryInitialized = $container->get('sentry.initialized');
        if ($sentryInitialized) {
            /** @var SentryHandler $sentryHandler */
            $sentryHandler = $container->get(SentryHandler::class);
            $logger->pushHandler($sentryHandler);
        }

        return $logger;
    },

    'logger' => [
        'file' => 'php://stderr',
    ],
];
