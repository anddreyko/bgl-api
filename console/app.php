<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require_once __DIR__ . '/../config/container.php';

$app = new Application('Console');

foreach ($container->get('console')['commands'] ?? [] as $command) {
    $app->add($container->get($command));
}

$app->run();
