<?php

declare(strict_types=1);

use Bgl\Core\Auth\Authenticator;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;
use Psr\Container\ContainerInterface;

return [
    AuthInterceptor::class => static function (ContainerInterface $container): AuthInterceptor {
        /** @var Authenticator $authenticator */
        $authenticator = $container->get(Authenticator::class);

        return new AuthInterceptor($authenticator);
    },
];
