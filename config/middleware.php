<?php

declare(strict_types=1);

use Slim\Middleware\ErrorMiddleware;

return static function (Slim\App $app) {
    $app->add(ErrorMiddleware::class);
};
