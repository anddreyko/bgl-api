<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\Console\Command\SchemaTool;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;

return [
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
        ],
        'fixture_paths' => [
            __DIR__ . '/../../src/Auth/Fixture',
        ],
    ],
];
