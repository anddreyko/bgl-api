<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Psr\Container\ContainerInterface;

return [
    Configuration::class => static function (ContainerInterface $container) {
        /**
         * @var array{
         *     namespace: string,
         *     dir: string,
         *     tableName: string,
         *     allOrNothing: bool,
         *     checkPlatform: bool
         * } $config
         */
        $config = $container->get('migrations');

        $configuration = new Configuration();

        $configuration->addMigrationsDirectory($config['namespace'], $config['dir']);
        $configuration->setAllOrNothing($config['allOrNothing']);
        $configuration->setCheckDatabasePlatform($config['checkPlatform']);

        $storageConfiguration = new TableMetadataStorageConfiguration();
        $storageConfiguration->setTableName($config['tableName']);

        $configuration->setMetadataStorageConfiguration($storageConfiguration);

        return $configuration;
    },

    'migrations' => [
        'namespace' => 'Migrations',
        'dir' => __DIR__ . '/../../migrations',
        'tableName' => 'migration',
        'allOrNothing' => true,
        'checkPlatform' => false,
    ],
];
