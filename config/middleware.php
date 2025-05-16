<?php

declare(strict_types=1);

return static function (Slim\App $app) {
    $app->add(\App\Application\Middlewares\EmptyFilesMiddleware::class);
    $app->add(\App\Application\Middlewares\TrimMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->add(\App\Application\Middlewares\AuthorizationMiddleware::class);
    $app->add(\App\Application\Middlewares\TranslatorMiddleware::class);
    $app->add(\App\Application\Middlewares\LocaleMiddleware::class);
    $app->addRoutingMiddleware();
    $app->add(\App\Application\Middlewares\ExceptionMiddleware::class);
    $app->add(Slim\Middleware\ErrorMiddleware::class);
};
