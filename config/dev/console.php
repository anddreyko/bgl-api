<?php

declare(strict_types=1);

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
            Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand::class,
            Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand::class,
            Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand::class,
            Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand::class,
            Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand::class,
            Doctrine\ORM\Tools\Console\Command\RunDqlCommand::class,
            Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand::class,
            Doctrine\ORM\Tools\Console\Command\InfoCommand::class,
            Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand::class,
        ],
    ],
];
