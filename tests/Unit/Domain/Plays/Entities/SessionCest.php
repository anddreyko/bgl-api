<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Session;
use Bgl\Domain\Plays\Entities\SessionStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Entities\Session
 */
#[Group('plays', 'session')]
final class SessionCest
{
    public function testOpenCreatesSessionWithDraftStatus(UnitTester $i): void
    {
        $id = new Uuid('session-id');
        $userId = 'user-123';
        $name = 'Friday night game';
        $startedAt = new \DateTimeImmutable('2024-06-15 20:00:00');

        $session = Session::open($id, $userId, $name, $startedAt);

        $i->assertSame($id, $session->getId());
        $i->assertSame($userId, $session->getUserId());
        $i->assertSame($name, $session->getName());
        $i->assertSame(SessionStatus::Draft, $session->getStatus());
        $i->assertSame($startedAt, $session->getStartedAt());
        $i->assertNull($session->getFinishedAt());
    }

    public function testOpenCreatesSessionWithNullName(UnitTester $i): void
    {
        $id = new Uuid('session-id');
        $userId = 'user-456';
        $startedAt = new \DateTimeImmutable('2024-06-15 20:00:00');

        $session = Session::open($id, $userId, null, $startedAt);

        $i->assertNull($session->getName());
        $i->assertSame(SessionStatus::Draft, $session->getStatus());
    }

    public function testGetIdReturnsUuid(UnitTester $i): void
    {
        $id = new Uuid('test-uuid');
        $session = Session::open($id, 'user-1', null, new \DateTimeImmutable());

        $i->assertSame('test-uuid', $session->getId()->getValue());
    }

    public function testGetUserIdReturnsUserId(UnitTester $i): void
    {
        $session = Session::open(
            new Uuid('id'),
            'user-abc',
            null,
            new \DateTimeImmutable(),
        );

        $i->assertSame('user-abc', $session->getUserId());
    }
}
