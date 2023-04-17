<?php

declare(strict_types=1);

use App\Console\FixturesLoadCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\SchemaTool;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Psr\Container\ContainerInterface;

return [
    FixturesLoadCommand::class => static function (ContainerInterface $container) {
        /** @var array{ console: class-string[], fixture_paths: string[] } $config */
        $config = $container->get('console');

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        return new FixturesLoadCommand($em, $config['fixture_paths']);
    },

    'console' => [
        'commands' => [
            Doctrine\ORM\Tools\Console\Command\ClearCache\CollectionRegionCommand::class,
            Doctrine\ORM\Tools\Console\Command\ClearCache\EntityRegionCommand::class,
            Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand::class,
            Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand::class,
            Doctrine\ORM\Tools\Console\Command\ClearCache\QueryRegionCommand::class,
            Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand::class,
            Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand::class,
            Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand::class,
            Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand::class,
            Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand::class,
            Doctrine\ORM\Tools\Console\Command\RunDqlCommand::class,
            Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand::class,
            Doctrine\ORM\Tools\Console\Command\InfoCommand::class,
            Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand::class,

            DropCommand::class,
            SchemaTool\DropCommand::class,

            Doctrine\Migrations\Tools\Console\Command\CurrentCommand::class,
            Doctrine\Migrations\Tools\Console\Command\DiffCommand::class,
            Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand::class,
            Doctrine\Migrations\Tools\Console\Command\GenerateCommand::class,
            Doctrine\Migrations\Tools\Console\Command\ListCommand::class,
            Doctrine\Migrations\Tools\Console\Command\RollupCommand::class,
            Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand::class,
            Doctrine\Migrations\Tools\Console\Command\VersionCommand::class,

            FixturesLoadCommand::class,
        ],
        'fixture_paths' => [
            __DIR__ . '/../../src/Auth/Fixtures',
        ],
    ],
];
