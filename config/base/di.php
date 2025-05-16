<?php

declare(strict_types=1);

use App\Domain\Auth\Repositories\DbUserRepository;
use App\Domain\Auth\Repositories\UserRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

return [
    ResponseFactoryInterface::class => DI\get(ResponseFactory::class),

    UserRepository::class => DI\get(DbUserRepository::class),
];
