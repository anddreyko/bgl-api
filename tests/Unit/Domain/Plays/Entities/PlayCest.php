<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\PlayStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Entities\Play
 */
#[Group('plays', 'play')]
final class PlayCest
{
    public function testOpenCreatesPlayWithDraftStatus(UnitTester $i): void
    {
        $id = new Uuid('play-id');
        $userId = new Uuid('user-123');
        $name = 'Friday night game';
        $startedAt = new \DateTimeImmutable('2024-06-15 20:00:00');

        $play = Play::open($id, $userId, $name, $startedAt);

        $i->assertSame($id, $play->getId());
        $i->assertSame($userId, $play->getUserId());
        $i->assertSame($name, $play->getName());
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
        $i->assertSame($startedAt, $play->getStartedAt());
        $i->assertNull($play->getFinishedAt());
    }

    public function testOpenCreatesPlayWithNullName(UnitTester $i): void
    {
        $id = new Uuid('play-id');
        $userId = new Uuid('user-456');
        $startedAt = new \DateTimeImmutable('2024-06-15 20:00:00');

        $play = Play::open($id, $userId, null, $startedAt);

        $i->assertNull($play->getName());
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testGetIdReturnsUuid(UnitTester $i): void
    {
        $id = new Uuid('test-uuid');
        $play = Play::open($id, new Uuid('user-1'), null, new \DateTimeImmutable());

        $i->assertSame('test-uuid', $play->getId()->getValue());
    }

    public function testGetUserIdReturnsUserId(UnitTester $i): void
    {
        $play = Play::open(
            new Uuid('id'),
            new Uuid('user-abc'),
            null,
            new \DateTimeImmutable(),
        );

        $i->assertSame('user-abc', $play->getUserId()->getValue());
    }

    public function testCloseChangesStatusToPublishedAndSetsFinishedAt(UnitTester $i): void
    {
        $play = Play::open(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Game night',
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $finishedAt = new \DateTimeImmutable('2024-06-15 23:00:00');
        $play->close($finishedAt);

        $i->assertSame(PlayStatus::Published, $play->getStatus());
        $i->assertSame($finishedAt, $play->getFinishedAt());
    }

    public function testCloseThrowsWhenPlayIsNotDraft(UnitTester $i): void
    {
        $play = Play::open(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $play->close(new \DateTimeImmutable('2024-06-15 23:00:00'));

        $i->expectThrowable(
            new \DomainException('Play can only be closed from draft status'),
            static function () use ($play): void {
                $play->close(new \DateTimeImmutable('2024-06-16 00:00:00'));
            },
        );
    }
}
