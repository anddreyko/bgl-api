<?php

declare(strict_types=1);

return static function (Slim\App $app): void {
    $app->add(\App\Application\Middleware\EmptyFilesMiddleware::class);
    $app->add(\App\Application\Middleware\TrimMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->add(\App\Application\Middleware\AuthorizationMiddleware::class);
    $app->add(\App\Application\Middleware\TranslatorMiddleware::class);
    $app->add(\App\Application\Middleware\LocaleMiddleware::class);
    $app->addRoutingMiddleware();
    $app->add(\App\Application\Middleware\ExceptionMiddleware::class);
    $app->add(Slim\Middleware\ErrorMiddleware::class);
};
