<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\PlayDeletedException;
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

    public function testFinalizeSetsFinishedAtWithoutChangingStatus(UnitTester $i): void
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

        $i->assertSame(PlayStatus::Draft, $play->getStatus());
        $i->assertSame($finishedAt, $play->getFinishedAt());
    }

    public function testFinalizeWorksWhenPublished(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Published,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $finishedAt = new DateTime('2024-06-15 23:00:00');
        $play->finalize($finishedAt);

        $i->assertSame(PlayStatus::Published, $play->getStatus());
        $i->assertSame($finishedAt, $play->getFinishedAt());
    }

    public function testFinalizeThrowsWhenDeleted(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-15 23:00:00'));
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

    public function testUpdateWorksWhenPublished(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Published,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $newGameId = new Uuid('game-new');
        $play->update('Updated name', $newGameId, Visibility::Public);

        $i->assertSame('Updated name', $play->getName());
        $i->assertSame($newGameId, $play->getGameId());
        $i->assertSame(Visibility::Public, $play->getVisibility());
        $i->assertSame(PlayStatus::Published, $play->getStatus());
    }

    public function testUpdateThrowsWhenDeleted(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $i->expectThrowable(
            new PlayDeletedException('Deleted play cannot be updated.'),
            static function () use ($play): void {
                $play->update('name', null, Visibility::Private);
            },
        );
    }

    public function testUpdateStatusDraftToPublished(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->update(null, null, Visibility::Private, PlayStatus::Published);

        $i->assertSame(PlayStatus::Published, $play->getStatus());
    }

    public function testUpdateStatusPublishedToDraft(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Published,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $play->update(null, null, Visibility::Private, PlayStatus::Draft);

        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testUpdateStatusToDeletedThrows(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $play->update(null, null, Visibility::Private, PlayStatus::Deleted);
            },
        );
    }

    public function testUpdateStatusNullKeepsCurrent(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->update('name', null, Visibility::Private);

        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testDeleteFromDraftChangesStatusToDeleted(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->delete();

        $i->assertSame(PlayStatus::Deleted, $play->getStatus());
    }

    public function testDeleteFromPublishedChangesStatusToDeleted(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Published,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $play->delete();

        $i->assertSame(PlayStatus::Deleted, $play->getStatus());
    }

    public function testDeleteAlreadyDeletedThrows(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $play->delete();
            },
        );
    }

    public function testReplacePlayersRemovesOldAndAddsNew(UnitTester $i): void
    {
        $players = new InMemoryPlayers();
        $play = Play::create(
            new Uuid('play-id'),
            new Uuid('user-123'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $players,
        );

        $oldPlayer = Player::create(new Uuid('player-old'), $play, new Uuid('mate-old'), null, false, null);
        $play->addPlayer($oldPlayer);
        $i->assertSame(1, $play->getPlayers()->count());

        $newPlayers = new InMemoryPlayers();
        $newPlayer1 = Player::create(new Uuid('player-new-1'), $play, new Uuid('mate-new-1'), 10, true, 'blue');
        $newPlayer2 = Player::create(new Uuid('player-new-2'), $play, new Uuid('mate-new-2'), 5, false, 'red');
        $newPlayers->add($newPlayer1);
        $newPlayers->add($newPlayer2);

        $play->replacePlayers($newPlayers);

        $i->assertSame(2, $play->getPlayers()->count());
        $i->assertNull($play->getPlayers()->find((string) $oldPlayer->getId()));
        $i->assertNotNull($play->getPlayers()->find((string) $newPlayer1->getId()));
        $i->assertNotNull($play->getPlayers()->find((string) $newPlayer2->getId()));
    }

    public function testReplacePlayersOnDeletedThrows(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('play-id'),
            new Uuid('user-123'),
            null,
            PlayStatus::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $play->replacePlayers(new InMemoryPlayers());
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
