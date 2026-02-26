<?php

declare(strict_types=1);

namespace Bgl\Tests\Support;

use Psr\Container\ContainerInterface;

final class DiHelper
{
    private static ?ContainerInterface $container = null;

    public static function container(): ContainerInterface
    {
        self::$container ??= require __DIR__ . '/../../config/container.php';

        return self::$container;
    }

    public static function reset(): void
    {
        self::$container = null;
    }
}
