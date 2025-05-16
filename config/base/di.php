<?php

declare(strict_types=1);

use App\Contexts\Auth\Repositories\DbUserRepository;
use App\Contexts\Auth\Repositories\UserRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

return [
    ResponseFactoryInterface::class => DI\get(ResponseFactory::class),

    UserRepository::class => DI\get(DbUserRepository::class),
];
