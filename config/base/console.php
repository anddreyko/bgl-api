<?php

declare(strict_types=1);

use App\Console\HelloCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;

return [
    'console' => [
        'commands' => [
            ValidateSchemaCommand::class,
            HelloCommand::class,

            Doctrine\Migrations\Tools\Console\Command\ExecuteCommand::class,
            Doctrine\Migrations\Tools\Console\Command\ListCommand::class,
            Doctrine\Migrations\Tools\Console\Command\MigrateCommand::class,
            Doctrine\Migrations\Tools\Console\Command\StatusCommand::class,
            Doctrine\Migrations\Tools\Console\Command\UpToDateCommand::class,
        ],
    ],
];
