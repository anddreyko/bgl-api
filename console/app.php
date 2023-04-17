<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Doctrine\ORM\Tools\Console\Command\AbstractEntityManagerCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @var Container $container
 * @psalm-suppress UnnecessaryVarAnnotation
 */
$container = require_once __DIR__ . '/../config/container.php';

$app = new Application('Console');

$configuration = new Configuration();

$configuration->addMigrationsDirectory('Migrations', __DIR__ . '/../migrations');
$configuration->setAllOrNothing(true);
$configuration->setCheckDatabasePlatform(false);

$storageConfiguration = new TableMetadataStorageConfiguration();
$storageConfiguration->setTableName('migration');

$configuration->setMetadataStorageConfiguration($storageConfiguration);


/** @var EntityManagerProvider $entityManagerProvider */
$entityManagerProvider = $container->get(EntityManagerProvider::class);

$dependencyFactory = DependencyFactory::fromEntityManager(
    new ExistingConfiguration($configuration),
    new ExistingEntityManager($entityManagerProvider->getDefaultManager())
);

$app->setCatchExceptions(true);

/** @var class-string<Symfony\Component\Console\Command\Command> $name */
foreach ($container->get('console')['commands'] ?? [] as $name) {
    /** @var Symfony\Component\Console\Command\Command $command */
    $command = $container->get($name);
    if ($command instanceof DoctrineCommand) {
        /** @var Symfony\Component\Console\Command\Command $command */
        $command = $container->make($name, ['dependencyFactory' => $dependencyFactory]);
    } elseif ($command instanceof AbstractEntityManagerCommand) {
        /** @var Symfony\Component\Console\Command\Command $command */
        $command = $container->make($name, ['entityManagerProvider' => $entityManagerProvider]);
    }
    $app->add($command);
}

$app->run();
