<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Player\PlayersFactory;

final readonly class DoctrinePlayersFactory implements PlayersFactory
{
    #[\Override]
    public function createEmpty(): Players
    {
        return new PlayerCollection();
    }
}
