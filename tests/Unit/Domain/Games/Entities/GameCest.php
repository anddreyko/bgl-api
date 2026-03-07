<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Games\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Games\Game
 */
#[Group('games', 'game')]
final class GameCest
{
    public function testCreateReturnsGameWithCorrectData(UnitTester $i): void
    {
        $id = new Uuid('game-id');
        $bggId = 13;
        $name = 'Catan';
        $yearPublished = 1995;
        $createdAt = new DateTime('2026-01-15 20:00:00');

        $game = Game::create($id, $bggId, $name, $yearPublished, $createdAt);

        $i->assertSame($id, $game->getId());
        $i->assertSame($bggId, $game->getBggId());
        $i->assertSame($name, $game->getName());
        $i->assertSame($yearPublished, $game->getYearPublished());
        $i->assertSame($createdAt, $game->getCreatedAt());
        $i->assertSame($createdAt, $game->getUpdatedAt());
    }

    public function testCreateWithNullYearPublished(UnitTester $i): void
    {
        $game = Game::create(
            new Uuid('game-id'),
            42,
            'Unknown Game',
            null,
            new DateTime(),
        );

        $i->assertNull($game->getYearPublished());
    }

    public function testCreatePreservesAllFields(UnitTester $i): void
    {
        $id = new Uuid('game-id');
        $game = Game::create($id, 13, 'Catan', 1995, new DateTime('2026-01-01 00:00:00'));

        $i->assertSame(13, $game->getBggId());
        $i->assertSame('Catan', $game->getName());
        $i->assertSame(1995, $game->getYearPublished());
        $i->assertSame($game->getCreatedAt(), $game->getUpdatedAt());
    }
}
