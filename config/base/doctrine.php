<?php

declare(strict_types=1);

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return [
    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManagerInterface {
        /**
         * @var array{
         *     metadata_dirs: string[],
         *     dev_mode: bool,
         *     proxy_dir: string,
         *     types: array<string, class-string<Doctrine\DBAL\Types\Type>>,
         *     subscribers: string[],
         *     connection: array{
         *         charset?: string,
         *         dbname?: string,
         *         defaultTableOptions?: array<string, mixed>,
         *         default_dbname?: string,
         *         driver?: 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv',
         *         driverClass?: class-string<Doctrine\DBAL\Driver>,
         *         driverOptions?: array<array-key, mixed>,
         *         host?: string,
         *         keepReplica?: bool,
         *         keepSlave?: bool,
         *         master?: array{
         *             charset?: string,
         *             dbname?: string,
         *             default_dbname?: string,
         *             driver?: 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv',
         *             driverClass?: class-string<Doctrine\DBAL\Driver>,
         *             driverOptions?: array<array-key, mixed>,
         *             host?: string,
         *             password?: string,
         *             path?: string,
         *             pdo?: PDO,
         *             platform?: Doctrine\DBAL\Platforms\AbstractPlatform,
         *             port?: int,
         *             unix_socket?: string,
         *             url?: string,
         *             user?: string
         *         },
         *         memory?: bool,
         *         password?: string,
         *         path?: string,
         *         pdo?: PDO,
         *         platform?: Doctrine\DBAL\Platforms\AbstractPlatform,
         *         port?: int,
         *         primary?: array{
         *             charset?: string,
         *             dbname?: string,
         *             default_dbname?: string,
         *             driver?: 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv',
         *             driverClass?: class-string<Doctrine\DBAL\Driver>,
         *             driverOptions?: array<array-key, mixed>,
         *             host?: string,
         *             password?: string,
         *             path?: string,
         *             pdo?: PDO,
         *             platform?: Doctrine\DBAL\Platforms\AbstractPlatform,
         *             port?: int,
         *             unix_socket?: string,
         *             url?: string,
         *             user?: string
         *         },
         *         replica?: array<array-key, array{
         *             charset?: string,
         *             dbname?: string,
         *             default_dbname?: string,
         *             driver?: 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv',
         *             driverClass?: class-string<Doctrine\DBAL\Driver>,
         *             driverOptions?: array<array-key, mixed>,
         *             host?: string,
         *             password?: string,
         *             path?: string,
         *             pdo?: PDO,
         *             platform?: Doctrine\DBAL\Platforms\AbstractPlatform,
         *             port?: int,
         *             unix_socket?: string,
         *             url?: string,
         *             user?: string
         *         }>,
         *         serverVersion?: string,
         *         sharding?: array<string, mixed>,
         *         slaves?: array<array-key, array{
         *             charset?: string,
         *             dbname?: string,
         *             default_dbname?: string,
         *             driver?: 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv',
         *             driverClass?: class-string<Doctrine\DBAL\Driver>,
         *             driverOptions?: array<array-key, mixed>,
         *             host?: string,
         *             password?: string,
         *             path?: string,
         *             pdo?: PDO,
         *             platform?: Doctrine\DBAL\Platforms\AbstractPlatform,
         *             port?: int,
         *             unix_socket?: string,
         *             url?: string,
         *             user?: string
         *         }>,
         *         unix_socket?: string,
         *         url?: string,
         *         user?: string,
         *         wrapperClass?: class-string<Doctrine\DBAL\Connection>
         *     },
         *     cache_dir: string,
         * } $settings
         */
        $settings = $container->get('doctrine');

        $config = ORMSetup::createAttributeMetadataConfiguration(
            $settings['metadata_dirs'],
            $settings['dev_mode'],
            $settings['proxy_dir'],
            $settings['cache_dir'] ? new FilesystemAdapter('', 0, $settings['cache_dir']) : new ArrayAdapter()
        );

        $config->setNamingStrategy(new UnderscoreNamingStrategy());

        foreach ($settings['types'] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $eventManager = new EventManager();
        foreach ($settings['subscribers'] as $name) {
            /** @var EventSubscriber $subscriber */
            $subscriber = $container->get($name);
            $eventManager->addEventSubscriber($subscriber);
        }

        return new EntityManager(
            conn: DriverManager::getConnection(
                params: $settings['connection'],
                eventManager: $eventManager
            ),
            config: $config
        );
    },

    EntityManagerProvider::class => static function (ContainerInterface $container): EntityManagerProvider {
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        return new SingleManagerProvider($em);
    },

    'doctrine' => [
        'dev_mode' => false,
        'cache_dir' => __DIR__ . '/../../var/cache/doctrine/cache',
        'proxy_dir' => __DIR__ . '/../../var/cache/doctrine/proxy',
        'connection' => [
            'driver' => 'pdo_pgsql',
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'dbname' => getenv('DB_NAME'),
            'charset' => 'utf-8',
        ],
        'subscribers' => [],
        'metadata_dirs' => [
            __DIR__ . '/../../src/Http/Entities',
        ],
        'types' => [
        ],
    ],
];
