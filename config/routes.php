<?php

declare(strict_types=1);

use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (Slim\App $app) {
    $app->get('/', Actions\SwaggerAction::class);

    $app->group('/v1', function (RouteCollectorProxyInterface $group) {
        $group->get('/hello-world', Actions\V1\HelloWorldAction::class);

        $group->group('/auth', function (RouteCollectorProxyInterface $group) {
            $group->get('/register-by-email', Actions\V1\Auth\SignUpAction::class);
            $group->get('/confirm-email', Actions\V1\Auth\ConfirmEmailAction::class);
            $group->get('/login-by-email', Actions\V1\Auth\SignInAction::class);
        });
    });
};
