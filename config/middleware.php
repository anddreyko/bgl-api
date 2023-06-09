<?php

declare(strict_types=1);

return static function (Slim\App $app) {
    $app->add(App\Core\Http\Middlewares\EmptyFilesMiddleware::class);
    $app->add(App\Core\Http\Middlewares\TrimMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->add(App\Core\Http\Middlewares\AuthorizationMiddleware::class);
    $app->add(App\Core\Http\Middlewares\TranslatorMiddleware::class);
    $app->add(App\Core\Http\Middlewares\LocaleMiddleware::class);
    $app->addRoutingMiddleware();
    $app->add(App\Core\Http\Middlewares\ExceptionMiddleware::class);
    $app->add(Slim\Middleware\ErrorMiddleware::class);
};
