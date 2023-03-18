<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

http_response_code(500);

require_once __DIR__ . '/../vendor/autoload.php';

$container = require_once __DIR__ . '/../config/container.php';

$app = AppFactory::createFromContainer($container);

(require_once __DIR__ . '/../config/middleware.php')($app, $container);
(require_once __DIR__ . '/../config/routes.php')($app);

$app->run();
