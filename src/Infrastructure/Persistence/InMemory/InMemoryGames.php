<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;

/**
 * @extends InMemoryRepository<Game>
 */
final class InMemoryGames extends InMemoryRepository implements Games
{
    public function __construct()
    {
        parent::__construct(new AnyFieldAccessor());
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByBggId(int $bggId): ?Game
    {
        foreach ($this->getEntities() as $game) {
            if ($game->getBggId() === $bggId) {
                return $game;
            }
        }

        return null;
    }
}
