<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\MessageBus;

use Bgl\Infrastructure\MessageBus\Tactician\TacticianCommandNameExtractor;
use Bgl\Infrastructure\MessageBus\Tactician\TacticianDispatcher;
use Bgl\Infrastructure\MessageBus\Tactician\TacticianWrapMiddleware;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\IntegrationTester;
use Bgl\Tests\Support\Messages\LoggingTactician;
use Bgl\Tests\Support\Messages\Ping;
use Bgl\Tests\Support\Messages\PingTacticianHandler;
use Codeception\Attribute\Group;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;

/**
 * @covers \Bgl\Infrastructure\MessageBus\Tactician\TacticianDispatcher
 * @covers \Bgl\Infrastructure\MessageBus\Tactician\TacticianWrapMiddleware
 */
#[Group('messages')]
final class TacticianDispatcherCest extends BaseDispatcher
{
    #[\Override]
    protected function dispatcherClass(): string
    {
        return TacticianDispatcher::class;
    }

    public function testTacticianHandler(IntegrationTester $i): void
    {
        $container = DiHelper::container();

        $commandBus = new CommandBus([
            new TacticianWrapMiddleware(LoggingTactician::class, $container),
            new CommandHandlerMiddleware(
                new TacticianCommandNameExtractor(),
                new InMemoryLocator(
                    [Ping::class => $container->get(PingTacticianHandler::class)],
                ),
                new InvokeInflector()
            ),
        ]);

        $commandBus->handle(new Ping('test'));

        $i->seeLoggerHasInfoThatContains('message id: ');
        $i->seeLoggerHasInfoThatContains('test');
    }
}
