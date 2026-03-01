<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\PlayNotDraftException;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Domain\Plays\Visibility;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Play
 */
#[Group('plays', 'play')]
final class PlayCest
{
    public function testOpenCreatesPlayWithDraftStatus(UnitTester $i): void
    {
        $id = new Uuid('play-id');
        $userId = new Uuid('user-123');
        $name = 'Friday night game';
        $startedAt = new DateTime('2024-06-15 20:00:00');

        $play = Play::create($id, $userId, $name, $startedAt, new InMemoryPlayers());

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
        $startedAt = new DateTime('2024-06-15 20:00:00');

        $play = Play::create($id, $userId, null, $startedAt, new InMemoryPlayers());

        $i->assertNull($play->getName());
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testGetIdReturnsUuid(UnitTester $i): void
    {
        $id = new Uuid('test-uuid');
        $play = Play::create($id, new Uuid('user-1'), null, new DateTime('now'), new InMemoryPlayers());

        $i->assertSame('test-uuid', $play->getId()->getValue());
    }

    public function testGetUserIdReturnsUserId(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('id'),
            new Uuid('user-abc'),
            null,
            new DateTime('now'),
            new InMemoryPlayers(),
        );

        $i->assertSame('user-abc', $play->getUserId()->getValue());
    }

    public function testFinalizeChangesStatusToPublishedAndSetsFinishedAt(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $finishedAt = new DateTime('2024-06-15 23:00:00');
        $play->finalize($finishedAt);

        $i->assertSame(PlayStatus::Published, $play->getStatus());
        $i->assertSame($finishedAt, $play->getFinishedAt());
    }

    public function testFinalizeThrowsWhenPlayIsNotDraft(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->finalize(new DateTime('2024-06-15 23:00:00'));

        $i->expectThrowable(
            new PlayNotDraftException('Play can only be finalized from draft status.'),
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-16 00:00:00'));
            },
        );
    }

    public function testCreateWithAllFields(UnitTester $i): void
    {
        $id = new Uuid('play-id');
        $userId = new Uuid('user-123');
        $gameId = new Uuid('game-456');
        $startedAt = new DateTime('2024-06-15 20:00:00');
        $players = new InMemoryPlayers();

        $play = Play::create(
            $id,
            $userId,
            'Game night',
            $startedAt,
            $players,
            $gameId,
            Visibility::Participants,
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
        $i->assertSame(Visibility::Participants, $play->getVisibility());
        $i->assertSame(1, $play->getPlayers()->count());
        $i->assertSame($player, $play->getPlayers()->find((string) $player->getId()));
    }

    public function testUpdateChangesFields(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Old name',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
            new Uuid('game-old'),
            Visibility::Private,
        );

        $newGameId = new Uuid('game-new');
        $play->update('New name', $newGameId, Visibility::Participants);

        $i->assertSame('New name', $play->getName());
        $i->assertSame($newGameId, $play->getGameId());
        $i->assertSame(Visibility::Participants, $play->getVisibility());
    }

    public function testUpdateWithNulls(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Some name',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
            new Uuid('game-id'),
        );

        $play->update(null, null, Visibility::Public);

        $i->assertNull($play->getName());
        $i->assertNull($play->getGameId());
        $i->assertSame(Visibility::Public, $play->getVisibility());
    }

    public function testUpdateThrowsWhenNotDraft(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->finalize(new DateTime('2024-06-15 23:00:00'));

        $i->expectThrowable(
            new PlayNotDraftException('Play can only be updated in draft status.'),
            static function () use ($play): void {
                $play->update('name', null, Visibility::Private);
            },
        );
    }

    public function testCreateWithDefaults(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->assertNull($play->getGameId());
        $i->assertSame(Visibility::Private, $play->getVisibility());
        $i->assertSame(0, $play->getPlayers()->count());
    }
}
