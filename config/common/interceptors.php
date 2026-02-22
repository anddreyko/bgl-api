<?php

declare(strict_types=1);

use Bgl\Core\Security\TokenGenerator;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;
use Psr\Container\ContainerInterface;

return [
    AuthInterceptor::class => static function (ContainerInterface $container): AuthInterceptor {
        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = $container->get(TokenGenerator::class);

        return new AuthInterceptor($tokenGenerator);
    },
];
