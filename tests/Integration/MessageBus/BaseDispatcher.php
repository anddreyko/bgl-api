<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\MessageBus;

use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Messages\MessageIdGenerator;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\IntegrationTester;
use Bgl\Tests\Support\Messages\GetTimestamp;
use Bgl\Tests\Support\Messages\GetTimestampHandler;
use Bgl\Tests\Support\Messages\Logging;
use Bgl\Tests\Support\Messages\Ping;
use Bgl\Tests\Support\Messages\PingHandler;
use Bgl\Tests\Support\Messages\Pong;
use Bgl\Tests\Support\Messages\PongHandler;

/**
 * @covers \Bgl\Core\Messages\Dispatcher
 */
abstract class BaseDispatcher
{
    private Dispatcher $dispatcher;

    public function _before(): void
    {
        $container = DiHelper::container();

        $class = $this->dispatcherClass();
        $this->dispatcher = new $class(
            handlers: [
                [Ping::class, PingHandler::class],
                [Pong::class, PongHandler::class],
                [GetTimestamp::class, GetTimestampHandler::class],
            ],
            middleware: [
                Logging::class,
            ],
            messageIdGenerator: $container->get(MessageIdGenerator::class),
            container: $container
        );
    }

    abstract protected function dispatcherClass(): string;

    public function testCommand(IntegrationTester $i): void
    {
        $result = $this->dispatcher->dispatch(new Ping('Test'));

        $i->assertEquals('Test', $result);
    }

    public function testEvent(IntegrationTester $i): void
    {
        $result = $this->dispatcher->dispatch(new Pong('Test'));

        $i->assertEquals(null, $result);
        $i->seeLoggerHasInfoThatContains('Test');
    }

    public function testQuery(IntegrationTester $i): void
    {
        $result = $this->dispatcher->dispatch(new GetTimestamp('2025-11-11'));

        $i->assertEquals('11.11.2025', $result->format('d.m.Y'));
    }

    public function testMiddleware(IntegrationTester $i): void
    {
        $this->dispatcher->dispatch(new GetTimestamp('2025-11-11'));

        $i->seeLoggerHasInfoThatContains('message id: ');
    }
}
