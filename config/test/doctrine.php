<?php

declare(strict_types=1);

use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\EmailConfirmationTokenMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\VerificationTokenMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Games\GameMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Mates\MateMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayerMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\PasskeyChallengeMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\PasskeyMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\UserMapping;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

$chain = new MappingDriverChain();
$chain->addDriver(
    new PhpMappingDriver([
        new UserMapping(),
        new PasskeyMapping(),
        new PasskeyChallengeMapping(),
        new PlayMapping(),
        new PlayerMapping(),
        new MateMapping(),
        new GameMapping(),
    ]),
    'Bgl\\Domain'
);
$chain->addDriver(
    new PhpMappingDriver([
        new EmailConfirmationTokenMapping(),
        new VerificationTokenMapping(),
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
