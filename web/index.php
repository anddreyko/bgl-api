<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

http_response_code(500);

require_once __DIR__ . '/../vendor/autoload.php';

/** @var ?string $sentry */
$sentry = env('SENTRY_DSN');
if (null !== $sentry) {
    Sentry\init(['dsn' => $sentry]);
}

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

/** @var \Slim\App<ContainerInterface|null> $app */
$app = AppFactory::createFromContainer($container);

(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

$app->run();
