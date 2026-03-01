<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Domain\Plays\Entities\Players;

/**
 * @extends InMemoryRepository<Player>
 */
final class InMemoryPlayers extends InMemoryRepository implements Players
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
    public function getIterator(): \ArrayIterator
    {
        /** @var \ArrayIterator<int, Player> */
        return new \ArrayIterator(array_values($this->getEntities()));
    }
}
