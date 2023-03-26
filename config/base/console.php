<?php

declare(strict_types=1);

use App\Console\HelloCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;

return [
    'console' => [
        'commands' => [
            ValidateSchemaCommand::class,
            HelloCommand::class,
        ],
    ],
];
