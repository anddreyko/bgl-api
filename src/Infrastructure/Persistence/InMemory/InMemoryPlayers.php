<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\Players;

/**
 * @extends InMemoryRepository<Player>
 */
final class InMemoryPlayers extends InMemoryRepository implements Players
{
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        /** @var \ArrayIterator<int, Player> */
        return new \ArrayIterator(array_values($this->getEntities()));
    }
}
