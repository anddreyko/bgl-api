<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(
    new ConfigAggregator([
        new PhpFileProvider(__DIR__ . '/common/*.php'),
        new PhpFileProvider(__DIR__ . '/common/**/*.php'),
        new PhpFileProvider(__DIR__ . '/' . ((string)getenv('APP_ENV') ?: 'prod') . '/*.php'),
        new PhpFileProvider(__DIR__ . '/' . ((string)getenv('APP_ENV') ?: 'prod') . '/**/*.php'),
    ])->getMergedConfig()
);

return $containerBuilder->build();
