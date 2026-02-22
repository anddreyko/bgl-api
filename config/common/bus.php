<?php

declare(strict_types=1);

use Bgl\Application\Aspects;
use Bgl\Application\Handlers;
use Bgl\Application\Handlers\Auth;
use Bgl\Application\Handlers\Plays;
use Bgl\Application\Handlers\User;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Messages\EnvelopeFactory;
use Bgl\Core\Messages\Message;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Messages\MessageIdGenerator;
use Bgl\Core\Messages\MessageMiddleware;
use Bgl\Infrastructure\MessageBus\Tactician\TacticianDispatcher;
use Psr\Container\ContainerInterface;

return [
    Dispatcher::class => static function (ContainerInterface $container): Dispatcher {
        /**
         * @var array{
         *     handlers: list<array{0: class-string<Message>, 1: class-string<MessageHandler>, 2: MessageMiddleware[]}>,
         *     middleware: list<class-string<\Bgl\Core\Messages\MessageMiddleware>>,
         * } $config
         */
        $config = $container->get('bus');
        /** @var MessageIdGenerator $generator */
        $generator = $container->get(MessageIdGenerator::class);

        return new TacticianDispatcher(
            handlers: $config['handlers'],
            middleware: $config['middleware'],
            messageIdGenerator: $generator,
            envelopeFactory: new EnvelopeFactory(),
            container: $container
        );
    },

    'bus' => [
        'handlers' => [
            [Handlers\Ping\Command::class, Handlers\Ping\Handler::class],
            [Auth\Register\Command::class, Auth\Register\Handler::class],
            [Auth\ConfirmEmail\Command::class, Auth\ConfirmEmail\Handler::class],
            [Auth\LoginByCredentials\Command::class, Auth\LoginByCredentials\Handler::class],
            [Auth\RefreshToken\Command::class, Auth\RefreshToken\Handler::class],
            [Auth\SignOut\Command::class, Auth\SignOut\Handler::class],
            [User\GetUser\Query::class, User\GetUser\Handler::class],
            [Plays\OpenSession\Command::class, Plays\OpenSession\Handler::class],
        ],
        'middleware' => [
            Aspects\Logging::class,
            Aspects\Transactional::class,
        ],
    ],
];
