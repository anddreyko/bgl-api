<?php

declare(strict_types=1);

use Bgl\Tests\Support\Dummy\TestLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static fn(): LoggerInterface => new TestLogger(),
    TestLogger::class => static function (ContainerInterface $container): TestLogger {
        /** @var TestLogger */
        return $container->get(LoggerInterface::class);
    },
];
