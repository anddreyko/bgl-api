<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional;

use Bgl\Application\Aspects\Logging;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Bgl\Tests\Support\Messages\Ping;
use Bgl\Tests\Support\Messages\PingHandler;
use Bgl\Tests\Support\Messages\Thrown;
use Bgl\Tests\Support\Messages\ThrownHandler;
use Codeception\Attribute\Group;
use DateMalformedStringException;

/**
 * @covers \Bgl\Application\Aspects\Logging
 */
#[Group('application', 'aspect', 'logging')]
final class LoggingAspectCest
{
    public function testSuccess(FunctionalTester $i): void
    {
        $container = DiHelper::container();
        $logging = $container->get(Logging::class);

        $logging(new Envelope(new Ping('test'), '1'), new PingHandler());

        $i->seeLoggerHasInfoThatContains('Start handle Bgl\Tests\Support\Messages\Ping');
        $i->seeLoggerHasInfoThatContains('Finish handle Bgl\Tests\Support\Messages\Ping');
    }

    public function testError(FunctionalTester $i): void
    {
        $container = DiHelper::container();
        $logging = $container->get(Logging::class);

        $i->expectThrowable(
            DateMalformedStringException::class,
            static fn() => $logging(
                new Envelope(new Thrown(new DateMalformedStringException()), '2'),
                new ThrownHandler()
            )
        );

        $i->seeLoggerHasInfoThatContains('Start handle Bgl\Tests\Support\Messages\Thrown');
        $i->seeLoggerHasErrorThatContains('Error handle Bgl\Tests\Support\Messages\Thrown');
    }
}
