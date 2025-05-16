<?php

declare(strict_types=1);

use App\Application\Middleware\AuthorizationMiddleware;
use Slim\Interfaces\RouteCollectorProxyInterface;

return static function (Slim\App $app) {
    $app->get('/', \App\Presentation\Web\SwaggerAction::class)
        ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

    $app->group('/v1', function (RouteCollectorProxyInterface $group) {
        $group->get('/hello-world', \App\Presentation\Web\V1\HelloWorldAction::class)
            ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
        $group->get('/ping', \App\Presentation\Web\V1\PingAction::class)
            ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');

        $group->group('/auth', function (RouteCollectorProxyInterface $group) {
            $group->post('/sign-up-by-email', \App\Presentation\Web\V1\Auth\SignUpAction::class)
                ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->get('/confirm-by-email[/{token}]', \App\Presentation\Web\V1\Auth\ConfirmEmailAction::class)
                ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->post('/sign-in-by-email', \App\Presentation\Web\V1\Auth\SignInAction::class)
                ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            $group->post('/sign-out', \App\Presentation\Web\V1\Auth\SignOutAction::class);
            $group->post('/refresh', \App\Presentation\Web\V1\Auth\RefreshAction::class);
        });

        $group->group('/records', function (RouteCollectorProxyInterface $group) {
            $group->group('/sessions', function (RouteCollectorProxyInterface $group) {
                $group->post('', \App\Presentation\Web\V1\Plays\OpenSessionAction::class)
                    ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
                $group->patch('[/{id}]', \App\Presentation\Web\V1\Plays\CloseSessionAction::class)
                    ->setArgument(AuthorizationMiddleware::ATTRIBUTE_ACCESSED, '1');
            });
        });

        $group->group('/user', function (RouteCollectorProxyInterface $group) {
            $group->get('[/{id}]', \App\Presentation\Web\V1\User\InfoAction::class);
        });
    });
};
