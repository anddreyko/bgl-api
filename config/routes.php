<?php

declare(strict_types=1);

use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (Slim\App $app) {
    $app->get('/', Actions\SwaggerAction::class)
        ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

    $app->group('/v1', function (RouteCollectorProxyInterface $group) {
        $group->get('/hello-world', Actions\V1\HelloWorldAction::class)
            ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

        $group->group('/auth', function (RouteCollectorProxyInterface $group) {
            $group->get('/sign-up-by-email', Actions\V1\Auth\SignUpAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->get('/confirm-by-email[/{token}]', Actions\V1\Auth\ConfirmEmailAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->get('/sign-in-by-email', Actions\V1\Auth\SignInAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->post('/sign-out', Actions\V1\Auth\SignOutAction::class);
            $group->get('/refresh', Actions\V1\Auth\RefreshAction::class);
        });

        $group->group('/user', function (RouteCollectorProxyInterface $group) {
            $group->get('/info', Actions\V1\User\InfoAction::class);
        });
    });
};
