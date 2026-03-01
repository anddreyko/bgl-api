<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Player\PlayersFactory;

final readonly class InMemoryPlayersFactory implements PlayersFactory
{
    #[\Override]
    public function createEmpty(): Players
    {
        return new InMemoryPlayers();
    }
}
