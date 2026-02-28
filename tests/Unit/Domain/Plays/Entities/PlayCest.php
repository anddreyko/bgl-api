<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Domain\Plays\Entities\PlayStatus;
use Bgl\Domain\Plays\Entities\Visibility;
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

        $play = Play::create($id, $userId, $name, $startedAt);

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

        $play = Play::create($id, $userId, null, $startedAt);

        $i->assertNull($play->getName());
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testGetIdReturnsUuid(UnitTester $i): void
    {
        $id = new Uuid('test-uuid');
        $play = Play::create($id, new Uuid('user-1'), null, new \DateTimeImmutable());

        $i->assertSame('test-uuid', $play->getId()->getValue());
    }

    public function testGetUserIdReturnsUserId(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('id'),
            new Uuid('user-abc'),
            null,
            new \DateTimeImmutable(),
        );

        $i->assertSame('user-abc', $play->getUserId()->getValue());
    }

    public function testCloseChangesStatusToPublishedAndSetsFinishedAt(UnitTester $i): void
    {
        $play = Play::create(
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
        $play = Play::create(
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

    public function testCreateWithAllFields(UnitTester $i): void
    {
        $id = new Uuid('play-id');
        $userId = new Uuid('user-123');
        $gameId = new Uuid('game-456');
        $startedAt = new \DateTimeImmutable('2024-06-15 20:00:00');

        $play = Play::create(
            $id,
            $userId,
            'Game night',
            $startedAt,
            $gameId,
            Visibility::Friends,
        );

        $player = Player::create(
            new Uuid('player-1'),
            $play,
            new Uuid('mate-1'),
            10,
            true,
            'blue',
        );
        $play->addPlayer($player);

        $i->assertSame($id, $play->getId());
        $i->assertSame($userId, $play->getUserId());
        $i->assertSame('Game night', $play->getName());
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
        $i->assertSame($startedAt, $play->getStartedAt());
        $i->assertNull($play->getFinishedAt());
        $i->assertSame($gameId, $play->getGameId());
        $i->assertSame(Visibility::Friends, $play->getVisibility());
        $i->assertCount(1, $play->getPlayers());
        $i->assertSame($player, $play->getPlayers()[0]);
    }

    public function testCreateWithDefaults(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );

        $i->assertNull($play->getGameId());
        $i->assertSame(Visibility::Private, $play->getVisibility());
        $i->assertCount(0, $play->getPlayers());
    }
}
