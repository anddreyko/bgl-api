<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

return (new ConfigAggregator([
    new PhpFileProvider(__DIR__ . '/base/*.php'),
    new PhpFileProvider(__DIR__ . '/' . (getenv('APP_ENV') ?: 'prod') . '/*.php'),
]))->getMergedConfig();
