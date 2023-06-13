<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

http_response_code(500);

require_once __DIR__ . '/../vendor/autoload.php';

if (env('SENTRY_DSN')) {
    Sentry\init(['dsn' => env('SENTRY_DSN')]);
}

/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/../config/container.php';

$app = AppFactory::createFromContainer($container);

(require_once __DIR__ . '/../config/middleware.php')($app);
(require_once __DIR__ . '/../config/routes.php')($app);

$app->run();
