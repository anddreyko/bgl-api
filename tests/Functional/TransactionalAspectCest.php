<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional;

use Bgl\Application\Aspects\Transactional;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Persistence\TransactionManager;
use Bgl\Tests\Support\FunctionalTester;
use Bgl\Tests\Support\Messages\Ping;
use Bgl\Tests\Support\Messages\PingHandler;
use Bgl\Tests\Support\Messages\Thrown;
use Bgl\Tests\Support\Messages\ThrownHandler;
use Codeception\Attribute\Group;
use Codeception\Stub;
use DateMalformedStringException;

/**
 * @covers \Bgl\Application\Aspects\Transactional
 */
#[Group('application', 'aspect', 'transactional')]
final class TransactionalAspectCest
{
    public function testSuccessFlushesAndCommits(FunctionalTester $i): void
    {
        /** @var list<string> $calls */
        $calls = [];

        $tm = Stub::makeEmpty(TransactionManager::class, [
            'beginTransaction' => static function () use (&$calls): void {
                $calls[] = 'beginTransaction';
            },
            'flush' => static function () use (&$calls): void {
                $calls[] = 'flush';
            },
            'commit' => static function () use (&$calls): void {
                $calls[] = 'commit';
            },
            'rollback' => static function () use (&$calls): void {
                $calls[] = 'rollback';
            },
        ]);

        $transactional = new Transactional($tm);

        $result = $transactional(
            new Envelope(new Ping('hello'), '1'),
            new PingHandler(),
        );

        $i->assertSame('hello', $result);
        $i->assertSame(['beginTransaction', 'flush', 'commit'], $calls);
    }

    public function testExceptionRollsBackAndRethrows(FunctionalTester $i): void
    {
        /** @var list<string> $calls */
        $calls = [];

        $tm = Stub::makeEmpty(TransactionManager::class, [
            'beginTransaction' => static function () use (&$calls): void {
                $calls[] = 'beginTransaction';
            },
            'flush' => static function () use (&$calls): void {
                $calls[] = 'flush';
            },
            'commit' => static function () use (&$calls): void {
                $calls[] = 'commit';
            },
            'rollback' => static function () use (&$calls): void {
                $calls[] = 'rollback';
            },
        ]);

        $transactional = new Transactional($tm);

        $i->expectThrowable(
            DateMalformedStringException::class,
            static fn() => $transactional(
                new Envelope(new Thrown(new DateMalformedStringException()), '2'),
                new ThrownHandler(),
            ),
        );

        $i->assertSame(['beginTransaction', 'rollback'], $calls);
        $i->assertNotContains('flush', $calls);
        $i->assertNotContains('commit', $calls);
    }
}
