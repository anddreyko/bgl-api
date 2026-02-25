<?php

declare(strict_types=1);

use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\EmailConfirmationTokenMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\PasskeyChallengeMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\PasskeyMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayMapping;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

$chain = new MappingDriverChain();
$chain->addDriver(
    new PhpMappingDriver([
        new UserMapping(),
        new EmailConfirmationTokenMapping(),
        new PasskeyMapping(),
        new PasskeyChallengeMapping(),
        new PlayMapping(),
    ]),
    'Bgl\\Domain'
);
$chain->addDriver(
    new PhpMappingDriver([
        new EmailConfirmationTokenMapping(),
    ]),
    'Bgl\\Infrastructure\\Auth'
);
$chain->addDriver(
    new AttributeDriver(
        paths: [
            __DIR__ . '/../../tests/Support/Repositories',
        ]
    ),
    'Bgl\\Tests\\Support\\Repositories'
);

return [
    'doctrine' => [
        'db' => [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../var/db/sqlite',
        ],
        'mapping' => $chain,
    ],
];
