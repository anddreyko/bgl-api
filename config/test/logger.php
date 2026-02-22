<?php

declare(strict_types=1);

use Bgl\Tests\Support\Dummy\TestLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static fn(): LoggerInterface => new TestLogger(),
    TestLogger::class => static fn(ContainerInterface $container): TestLogger =>
        /** @var TestLogger */
        $container->get(LoggerInterface::class),
];
