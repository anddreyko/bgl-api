<?php

declare(strict_types=1);

use Bgl\Application\Aspects;
use Bgl\Application\Handlers;
use Bgl\Application\Handlers\Auth;
use Bgl\Application\Handlers\Games;
use Bgl\Application\Handlers\Locations;
use Bgl\Application\Handlers\Mates;
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
            [Auth\RegisterPasskeyOptions\Command::class, Auth\RegisterPasskeyOptions\Handler::class],
            [Auth\RegisterPasskeyVerify\Command::class, Auth\RegisterPasskeyVerify\Handler::class],
            [Auth\PasskeySignInOptions\Command::class, Auth\PasskeySignInOptions\Handler::class],
            [Auth\PasskeySignInVerify\Command::class, Auth\PasskeySignInVerify\Handler::class],
            [User\GetUser\Query::class, User\GetUser\Handler::class],
            [User\UpdateUser\Command::class, User\UpdateUser\Handler::class],
            [Plays\CreatePlay\Command::class, Plays\CreatePlay\Handler::class],
            [Plays\FinalizePlay\Command::class, Plays\FinalizePlay\Handler::class],
            [Plays\UpdatePlay\Command::class, Plays\UpdatePlay\Handler::class],
            [Plays\DeletePlay\Command::class, Plays\DeletePlay\Handler::class],
            [Plays\GetPlay\Query::class, Plays\GetPlay\Handler::class],
            [Plays\ListPlays\Query::class, Plays\ListPlays\Handler::class],
            [Mates\CreateMate\Command::class, Mates\CreateMate\Handler::class],
            [Mates\ListMates\Query::class, Mates\ListMates\Handler::class],
            [Mates\GetMate\Query::class, Mates\GetMate\Handler::class],
            [Mates\UpdateMate\Command::class, Mates\UpdateMate\Handler::class],
            [Mates\DeleteMate\Command::class, Mates\DeleteMate\Handler::class],
            [Games\SearchGames\Query::class, Games\SearchGames\Handler::class],
            [Games\GetGame\Query::class, Games\GetGame\Handler::class],
            [Locations\CreateLocation\Command::class, Locations\CreateLocation\Handler::class],
            [Locations\ListLocations\Query::class, Locations\ListLocations\Handler::class],
            [Locations\GetLocation\Query::class, Locations\GetLocation\Handler::class],
            [Locations\UpdateLocation\Command::class, Locations\UpdateLocation\Handler::class],
            [Locations\DeleteLocation\Command::class, Locations\DeleteLocation\Handler::class],
        ],
        'middleware' => [
            Aspects\Logging::class,
            Aspects\Transactional::class,
        ],
    ],
];
