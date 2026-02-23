<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional;

use Bgl\Application\Handlers\Ping\Command;
use Bgl\Application\Handlers\Ping\Handler;
use Bgl\Core\AppVersion;
use Bgl\Core\Clock;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use DI\Container;
use Psr\Clock\ClockInterface;

/**
 * @covers \Bgl\Application\Handlers\Ping\Handler
 */
#[Group('application', 'handler', 'ping')]
class PingHandlerCest
{
    public function testSuccess(FunctionalTester $i): void
    {
        $container = new Container([
            AppVersion::class => new AppVersion('2.0.0'),
            ClockInterface::class => new Clock(stub: new \DateTimeImmutable('2025-10-13')),
        ]);
        /** @var Handler $handler */
        $handler = $container->get(Handler::class);
        $envelope = new Envelope(
            message: new Command(datetime: new \DateTimeImmutable('2025-10-10')),
            messageId: '123',
            parentId: '234',
            traceId: '345',
        );

        $result = $handler($envelope);

        $i->assertEquals('123', $result->messageId);
        $i->assertEquals('234', $result->parentId);
        $i->assertEquals('345', $result->traceId);
        $i->assertEquals('2.0.0', $result->version);
        $i->assertEquals('test', $result->environment);
        $i->assertEquals('2025-10-13', $result->datetime->getFormattedValue('Y-m-d'));
        $i->assertEquals(3, $result->delay->getDays());
    }
}
