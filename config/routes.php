<?php

declare(strict_types=1);

use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (Slim\App $app) {
    $app->get('/', Actions\SwaggerAction::class)
        ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

    $app->group('/v1', function (RouteCollectorProxyInterface $group) {
        $group->get('/hello-world', Actions\V1\HelloWorldAction::class)
            ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
        $group->get('/ping', Actions\V1\PingAction::class)
            ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

        $group->group('/auth', function (RouteCollectorProxyInterface $group) {
            $group->post('/sign-up-by-email', Actions\V1\Auth\SignUpAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->get('/confirm-by-email[/{token}]', Actions\V1\Auth\ConfirmEmailAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->post('/sign-in-by-email', Actions\V1\Auth\SignInAction::class)
                ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->post('/sign-out', Actions\V1\Auth\SignOutAction::class);
            $group->post('/refresh', Actions\V1\Auth\RefreshAction::class);
        });

        $group->group('/records', function (RouteCollectorProxyInterface $group) {
            $group->group('/sessions', function (RouteCollectorProxyInterface $group) {
                $group->post('', Actions\V1\Plays\OpenSessionAction::class)
                    ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
                $group->patch('[/{id}]', Actions\V1\Plays\CloseSessionAction::class)
                    ->setArgument(App\Core\Http\Middlewares\AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            });
        });

        $group->group('/user', function (RouteCollectorProxyInterface $group) {
            $group->get('[/{id}]', Actions\V1\User\InfoAction::class);
        });
    });
};
