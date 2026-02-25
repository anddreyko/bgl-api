<?php

declare(strict_types=1);

use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\EmailConfirmationTokenMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\PasskeyChallengeMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\PasskeyMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Type\EmailType;
use Bgl\Infrastructure\Persistence\Doctrine\Type\UuidType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Psr\Container\ContainerInterface;

return [
    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManagerInterface {
        if (!Type::hasType('uuid_vo')) {
            Type::addType('uuid_vo', UuidType::class);
        }
        if (!Type::hasType('email_vo')) {
            Type::addType('email_vo', EmailType::class);
        }

        /** @var array{db: array{driver: string}, mapping: MappingDriver, proxy_dir: string} $config */
        $config = $container->get('doctrine');

        /** @psalm-suppress ArgumentTypeCoercion */
        $connection = DriverManager::getConnection($config['db']);

        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl($config['mapping']);
        $configuration->enableNativeLazyObjects(true);
        $configuration->setNamingStrategy(new UnderscoreNamingStrategy());
        $configuration->setProxyDir($config['proxy_dir']);

        return new EntityManager(
            conn: $connection,
            config: $configuration
        );
    },

    'doctrine' => [
        'dev_mode' => false,
        'cache_dir' => __DIR__ . '/../../var/cache/doctrine/cache',
        'proxy_dir' => __DIR__ . '/../../var/cache/doctrine/proxy',
        'proxy_generate' => null,
        'db' => [
            'driver' => 'pdo_pgsql',
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USER'),
            'port' => getenv('DB_PORT'),
            'password' => getenv('DB_PASS'),
            'dbname' => getenv('DB_NAME'),
            'charset' => 'utf-8',
        ],
        'mapping' => new PhpMappingDriver([
            new UserMapping(),
            new EmailConfirmationTokenMapping(),
            new PasskeyMapping(),
            new PasskeyChallengeMapping(),
            new PlayMapping(),
        ]),
    ],
];
