<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Games\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Game;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Games\Entities\Game
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
        $createdAt = new \DateTimeImmutable('2026-01-15 20:00:00');

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
            new \DateTimeImmutable(),
        );

        $i->assertNull($game->getYearPublished());
    }

    public function testUpdateFromCatalogChangesFields(UnitTester $i): void
    {
        $game = Game::create(
            new Uuid('game-id'),
            13,
            'Catan',
            1995,
            new \DateTimeImmutable('2026-01-01 00:00:00'),
        );

        $updatedAt = new \DateTimeImmutable('2026-02-01 12:00:00');
        $game->updateFromCatalog('Catan (6th Edition)', 2024, $updatedAt);

        $i->assertSame('Catan (6th Edition)', $game->getName());
        $i->assertSame(2024, $game->getYearPublished());
        $i->assertSame($updatedAt, $game->getUpdatedAt());
    }
}
