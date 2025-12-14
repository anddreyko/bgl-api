<?php

declare(strict_types=1);

use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

$chain = new MappingDriverChain();
$chain->addDriver(
    new PhpMappingDriver([
        new UserMapping(),
    ]),
    'Bgl\\Domain'
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
