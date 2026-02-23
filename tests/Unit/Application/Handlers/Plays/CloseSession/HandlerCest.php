<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Plays\CloseSession;

use Bgl\Application\Handlers\Plays\CloseSession\Command;
use Bgl\Application\Handlers\Plays\CloseSession\Handler;
use Bgl\Application\Handlers\Plays\CloseSession\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Session;
use Bgl\Domain\Plays\Entities\SessionStatus;
use Bgl\Domain\Plays\Entities\Sessions;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use Psr\Clock\ClockInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\CloseSession\Handler
 */
#[Group('plays', 'close-session')]
final class HandlerCest
{
    public function testSuccessfulClose(UnitTester $i): void
    {
        $session = Session::open(
            new Uuid('session-1'),
            new Uuid('user-123'),
            'Game night',
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $sessions = Stub::makeEmpty(Sessions::class, [
            'find' => static fn(): Session => $session,
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-06-15 23:00:00'),
        ]);

        $handler = new Handler($sessions, $clock);

        $command = new Command(sessionId: 'session-1', userId: 'user-123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('session-1', $result->sessionId);
        $i->assertSame('2024-06-15T20:00:00+00:00', $result->startedAt);
        $i->assertSame('2024-06-15T23:00:00+00:00', $result->finishedAt);
        $i->assertSame(SessionStatus::Published, $session->getStatus());
    }

    public function testCloseWithCustomFinishedAt(UnitTester $i): void
    {
        $session = Session::open(
            new Uuid('session-2'),
            new Uuid('user-123'),
            null,
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $sessions = Stub::makeEmpty(Sessions::class, [
            'find' => static fn(): Session => $session,
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class);

        $handler = new Handler($sessions, $clock);

        $customFinishedAt = new \DateTimeImmutable('2024-06-15 22:30:00');
        $command = new Command(
            sessionId: 'session-2',
            userId: 'user-123',
            finishedAt: $customFinishedAt,
        );
        $envelope = new Envelope($command, 'msg-2');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('2024-06-15T22:30:00+00:00', $result->finishedAt);
    }

    public function testSessionNotFoundThrowsDomainException(UnitTester $i): void
    {
        $sessions = Stub::makeEmpty(Sessions::class, [
            'find' => static fn(): ?Session => null,
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class);

        $handler = new Handler($sessions, $clock);

        $command = new Command(sessionId: 'non-existent', userId: 'user-123');
        $envelope = new Envelope($command, 'msg-3');

        $i->expectThrowable(
            new \DomainException('Session not found'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testAccessDeniedThrowsDomainException(UnitTester $i): void
    {
        $session = Session::open(
            new Uuid('session-3'),
            new Uuid('user-owner'),
            null,
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $sessions = Stub::makeEmpty(Sessions::class, [
            'find' => static fn(): Session => $session,
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class);

        $handler = new Handler($sessions, $clock);

        $command = new Command(sessionId: 'session-3', userId: 'user-other');
        $envelope = new Envelope($command, 'msg-4');

        $i->expectThrowable(
            new \DomainException('Access denied'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
