<?php

declare(strict_types=1);

use Bgl\Core\AppVersion;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Sentry\Monolog\Handler as SentryHandler;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

return [
    'sentry.initialized' => static function (ContainerInterface $container): bool {
        $dsn = getenv('SENTRY_DSN');
        if ($dsn === false || $dsn === '') {
            return false;
        }

        $environment = getenv('APP_ENV');

        /** @var AppVersion $appVersion */
        $appVersion = $container->get(AppVersion::class);

        \Sentry\init([
            'dsn' => $dsn,
            'environment' => $environment !== false ? $environment : 'prod',
            'release' => $appVersion->getVersion(),
            'ignore_exceptions' => [
                \DomainException::class,
                \InvalidArgumentException::class,
            ],
        ]);

        return true;
    },

    HubInterface::class => static function (ContainerInterface $container): HubInterface {
        /** @var bool $initialized */
        $initialized = $container->get('sentry.initialized');

        return SentrySdk::getCurrentHub();
    },

    SentryHandler::class => static function (ContainerInterface $container): SentryHandler {
        /** @var HubInterface $hub */
        $hub = $container->get(HubInterface::class);

        return new SentryHandler($hub, Level::Error);
    },
];
