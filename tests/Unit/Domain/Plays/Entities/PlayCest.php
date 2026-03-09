<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\FinishedAtBeforeStartedAtException;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\PlayDeletedException;
use Bgl\Domain\Plays\PlayLifecycle;
use Bgl\Domain\Plays\PlayNotCurrentException;
use Bgl\Domain\Plays\PlayNotDeletedException;
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
    public function testCreateReturnsCurrentLifecycle(UnitTester $i): void
    {
        $id = new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');
        $userId = new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e');
        $name = 'Friday night game';
        $startedAt = new DateTime('2024-06-15 20:00:00');

        $play = Play::create($id, $userId, $name, $startedAt, new InMemoryPlayers());

        $i->assertSame($id, $play->getId());
        $i->assertSame($userId, $play->getUserId());
        $i->assertSame($name, $play->getName());
        $i->assertSame(PlayLifecycle::Current, $play->getLifecycle());
        $i->assertSame($startedAt, $play->getStartedAt());
        $i->assertNull($play->getFinishedAt());
    }

    public function testCreateWithNullName(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('c3d4e5f6-a7b8-4c9d-8e1f-2a3b4c5d6e7f'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->assertNull($play->getName());
        $i->assertSame(PlayLifecycle::Current, $play->getLifecycle());
    }

    public function testGetIdReturnsUuid(UnitTester $i): void
    {
        $id = new Uuid('d4e5f6a7-b8c9-4d0e-9f2a-3b4c5d6e7f80');
        $play = Play::create($id, new Uuid('e5f6a7b8-c9d0-4e1f-aa3b-4c5d6e7f8091'), null, new DateTime('now'), new InMemoryPlayers());

        $i->assertSame('d4e5f6a7-b8c9-4d0e-9f2a-3b4c5d6e7f80', $play->getId()->getValue());
    }

    public function testGetUserIdReturnsUserId(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('f6a7b8c9-d0e1-4f2a-ab4c-5d6e7f809102'),
            new Uuid('01234567-89ab-4cde-8012-3456789abcde'),
            null,
            new DateTime('now'),
            new InMemoryPlayers(),
        );

        $i->assertSame('01234567-89ab-4cde-8012-3456789abcde', $play->getUserId()->getValue());
    }

    public function testFinalizeFromCurrentToFinishedWithFinishedAt(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $finishedAt = new DateTime('2024-06-15 23:00:00');
        $play->finalize($finishedAt);

        $i->assertSame(PlayLifecycle::Finished, $play->getLifecycle());
        $i->assertSame($finishedAt, $play->getFinishedAt());
    }

    public function testFinalizeFromCurrentToFinishedWithoutFinishedAt(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->finalize();

        $i->assertSame(PlayLifecycle::Finished, $play->getLifecycle());
        $i->assertNull($play->getFinishedAt());
    }

    public function testFinalizeFromFinishedThrowsPlayNotCurrentException(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Finished,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayNotCurrentException::class,
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-15 23:00:00'));
            },
        );
    }

    public function testFinalizeFromDeletedThrowsPlayDeletedException(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayNotCurrentException::class,
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-15 23:00:00'));
            },
        );
    }

    public function testFinalizeWithFinishedAtBeforeStartedAtThrows(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->expectThrowable(
            FinishedAtBeforeStartedAtException::class,
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-15 19:00:00'));
            },
        );
    }

    public function testFinalizeWithFinishedAtEqualToStartedAtThrows(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->expectThrowable(
            FinishedAtBeforeStartedAtException::class,
            static function () use ($play): void {
                $play->finalize(new DateTime('2024-06-15 20:00:00'));
            },
        );
    }

    public function testCreateWithAllFields(UnitTester $i): void
    {
        $id = new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');
        $userId = new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e');
        $gameId = new Uuid('11111111-1111-4111-8111-111111111111');
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
            new Uuid('55555555-5555-4555-8555-555555555551'),
            $play,
            new Uuid('66666666-6666-4666-8666-666666666661'),
            10,
            true,
            'blue',
        );
        $play->addPlayer($player);

        $i->assertSame($id, $play->getId());
        $i->assertSame($userId, $play->getUserId());
        $i->assertSame('Game night', $play->getName());
        $i->assertSame(PlayLifecycle::Current, $play->getLifecycle());
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
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Old name',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
            new Uuid('22222222-2222-4222-8222-222222222222'),
            Visibility::Private,
        );

        $newGameId = new Uuid('33333333-3333-4333-8333-333333333333');
        $play->update('New name', $newGameId, Visibility::Participants);

        $i->assertSame('New name', $play->getName());
        $i->assertSame($newGameId, $play->getGameId());
        $i->assertSame(Visibility::Participants, $play->getVisibility());
    }

    public function testUpdateWithNulls(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Some name',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
            new Uuid('44444444-4444-4444-8444-444444444444'),
        );

        $play->update(null, null, Visibility::Public);

        $i->assertNull($play->getName());
        $i->assertNull($play->getGameId());
        $i->assertSame(Visibility::Public, $play->getVisibility());
    }

    public function testUpdateWorksOnFinishedPlay(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Finished,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $newGameId = new Uuid('33333333-3333-4333-8333-333333333333');
        $play->update('Updated name', $newGameId, Visibility::Public);

        $i->assertSame('Updated name', $play->getName());
        $i->assertSame($newGameId, $play->getGameId());
        $i->assertSame(Visibility::Public, $play->getVisibility());
        $i->assertSame(PlayLifecycle::Finished, $play->getLifecycle());
    }

    public function testUpdateThrowsWhenDeleted(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $play->update('name', null, Visibility::Private);
            },
        );
    }

    public function testDeleteFromCurrentChangesLifecycleToDeleted(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $play->delete();

        $i->assertSame(PlayLifecycle::Deleted, $play->getLifecycle());
    }

    public function testDeleteFromFinishedChangesLifecycleToDeleted(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Finished,
            new DateTime('2024-06-15 20:00:00'),
            new DateTime('2024-06-15 23:00:00'),
        );

        $play->delete();

        $i->assertSame(PlayLifecycle::Deleted, $play->getLifecycle());
    }

    public function testDeleteAlreadyDeletedThrows(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
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

    public function testRestoreFromDeletedToFinished(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $play->restore();

        $i->assertSame(PlayLifecycle::Finished, $play->getLifecycle());
    }

    public function testRestoreFromCurrentThrowsPlayNotDeletedException(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->expectThrowable(
            PlayNotDeletedException::class,
            static function () use ($play): void {
                $play->restore();
            },
        );
    }

    public function testRestoreFromFinishedThrowsPlayNotDeletedException(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Finished,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayNotDeletedException::class,
            static function () use ($play): void {
                $play->restore();
            },
        );
    }

    public function testReplacePlayersRemovesOldAndAddsNew(UnitTester $i): void
    {
        $players = new InMemoryPlayers();
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $players,
        );

        $oldPlayer = Player::create(new Uuid('55555555-5555-4555-8555-555555555500'), $play, new Uuid('66666666-6666-4666-8666-666666666600'), null, false, null);
        $play->addPlayer($oldPlayer);
        $i->assertSame(1, $play->getPlayers()->count());

        $newPlayers = new InMemoryPlayers();
        $newPlayer1 = Player::create(new Uuid('55555555-5555-4555-8555-5555555555a1'), $play, new Uuid('66666666-6666-4666-8666-6666666666a1'), 10, true, 'blue');
        $newPlayer2 = Player::create(new Uuid('55555555-5555-4555-8555-5555555555a2'), $play, new Uuid('66666666-6666-4666-8666-6666666666a2'), 5, false, 'red');
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
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
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

    public function testAddPlayerOnDeletedThrows(UnitTester $i): void
    {
        $play = new Play(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            PlayLifecycle::Deleted,
            new DateTime('2024-06-15 20:00:00'),
            null,
        );

        $i->expectThrowable(
            PlayDeletedException::class,
            static function () use ($play): void {
                $player = Player::create(
                    new Uuid('55555555-5555-4555-8555-555555555551'),
                    $play,
                    new Uuid('66666666-6666-4666-8666-666666666661'),
                    10,
                    true,
                    'blue',
                );
                $play->addPlayer($player);
            },
        );
    }

    public function testCreateWithDefaults(UnitTester $i): void
    {
        $play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            null,
            new DateTime('2024-06-15 20:00:00'),
            new InMemoryPlayers(),
        );

        $i->assertNull($play->getGameId());
        $i->assertSame(Visibility::Private, $play->getVisibility());
        $i->assertSame(0, $play->getPlayers()->count());
    }
}
