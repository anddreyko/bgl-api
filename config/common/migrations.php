<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return [
    DependencyFactory::class => static function (ContainerInterface $container): DependencyFactory {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        return DependencyFactory::fromEntityManager(
            new ConfigurationArray([
                'migrations_paths' => [
                    'Bgl\\Infrastructure\\Database\\Migrations' =>
                        __DIR__ . '/../../src/Infrastructure/Database/Migrations',
                ],
                'table_storage' => [
                    'table_name' => 'migration',
                ],
            ]),
            new ExistingEntityManager($entityManager),
        );
    },

    'migrations:commands' => static function (ContainerInterface $container): array {
        /** @var DependencyFactory $dependencyFactory */
        $dependencyFactory = $container->get(DependencyFactory::class);

        return [
            new CurrentCommand($dependencyFactory),
            new DiffCommand($dependencyFactory),
            new ExecuteCommand($dependencyFactory),
            new GenerateCommand($dependencyFactory),
            new LatestCommand($dependencyFactory),
            new ListCommand($dependencyFactory),
            new MigrateCommand($dependencyFactory),
            new RollupCommand($dependencyFactory),
            new StatusCommand($dependencyFactory),
            new SyncMetadataCommand($dependencyFactory),
            new UpToDateCommand($dependencyFactory),
            new VersionCommand($dependencyFactory),
        ];
    },
];
