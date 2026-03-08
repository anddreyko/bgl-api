<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Games;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games as GameRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Game>
 */
final class Games extends DoctrineRepository implements GameRepository
{
    #[\Override]
    public function getType(): string
    {
        return Game::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'g';
    }

    #[\Override]
    public function findByBggId(int $bggId): ?Game
    {
        return $this->findOneBy(new Equals(new Field('bggId'), $bggId));
    }
}
