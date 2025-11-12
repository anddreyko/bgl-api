<?php

declare(strict_types=1);

namespace Bgl\Tests\Support;

use Psr\Container\ContainerInterface;

final class DiHelper
{
    public static function container(): ContainerInterface
    {
        static $container;

        $container ??= require __DIR__ . '/../../config/container.php';

        return $container;
    }
}
