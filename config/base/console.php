<?php

declare(strict_types=1);

return [
    'console' => [
        'commands' => [
            Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand::class,

            Doctrine\Migrations\Tools\Console\Command\ExecuteCommand::class,
            Doctrine\Migrations\Tools\Console\Command\ListCommand::class,
            Doctrine\Migrations\Tools\Console\Command\MigrateCommand::class,
            Doctrine\Migrations\Tools\Console\Command\StatusCommand::class,
            Doctrine\Migrations\Tools\Console\Command\UpToDateCommand::class,

            \App\Presentation\Cli\HelloCommand::class,
        ],
    ],
];
