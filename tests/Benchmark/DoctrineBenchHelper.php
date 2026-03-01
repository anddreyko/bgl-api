<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;

final class DoctrineBenchHelper
{
    private static ?ContainerInterface $container = null;
    private static bool $schemaCreated = false;

    public static function container(): ContainerInterface
    {
        if (self::$container === null) {
            putenv('APP_ENV=test');
            self::$container = require __DIR__ . '/../../config/container.php';
        }

        return self::$container;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function get(string $class): object
    {
        /** @var T */
        return self::container()->get($class);
    }

    public static function entityManager(): EntityManagerInterface
    {
        return self::get(EntityManagerInterface::class);
    }

    public static function createSchema(): void
    {
        if (self::$schemaCreated) {
            return;
        }

        $em = self::entityManager();
        $tool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        self::$schemaCreated = true;
    }

    public static function flush(): void
    {
        self::entityManager()->flush();
    }

    public static function clear(): void
    {
        self::entityManager()->clear();
    }

    public static function truncateAll(): void
    {
        $em = self::entityManager();
        $connection = $em->getConnection();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $classMetadata) {
            $connection->executeStatement('DELETE FROM ' . $classMetadata->getTableName());
        }

        $em->clear();
    }

    public static function reset(): void
    {
        self::$container = null;
        self::$schemaCreated = false;
    }
}
