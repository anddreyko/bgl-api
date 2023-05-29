<?php

declare(strict_types=1);

use Slim\Middleware\ErrorMiddleware;

return static function (Slim\App $app) {
    $app->add(App\Core\Http\Middlewares\ExceptionMiddleware::class);
    $app->add(App\Core\Http\Middlewares\EmptyFilesMiddleware::class);
    $app->add(App\Core\Http\Middlewares\TrimMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->add(ErrorMiddleware::class);
};
