<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Slim\App;

trait FixtureHelper
{
    private static ?App $app = null;
    private static ?ContainerInterface $container = null;

    private function loadFixture(?string $fixture = null): void
    {
        $container = $this->container();
        $loader = new Loader();

        if ($fixture) {
            $fixture = $container->get($fixture);
            $loader->addFixture($fixture);
        }

        $em = $container->get(EntityManagerInterface::class);
        $executor = new ORMExecutor($em, new ORMPurger());

        $executor->execute($loader->getFixtures());
    }

    private function container(): ContainerInterface
    {
        if (!static::$container) {
            static::$container = require __DIR__ . '/../../../config/container.php';
        }

        return static::$container;
    }
}
