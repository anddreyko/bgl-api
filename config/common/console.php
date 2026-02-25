<?php

declare(strict_types=1);

use Bgl\Presentation\Console\Commands\OpenApiExportCommand;
use Psr\Container\ContainerInterface;

return [
    OpenApiExportCommand::class => static function (ContainerInterface $container): OpenApiExportCommand {
        /** @var array<string, mixed> $config */
        $config = $container->get('openapi');

        return new OpenApiExportCommand($config);
    },
];
