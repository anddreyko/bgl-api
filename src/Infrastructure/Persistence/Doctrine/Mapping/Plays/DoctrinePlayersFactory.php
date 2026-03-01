<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Players;
use Bgl\Domain\Plays\Entities\PlayersFactory;

final readonly class DoctrinePlayersFactory implements PlayersFactory
{
    #[\Override]
    public function createEmpty(): Players
    {
        return new PlayerCollection();
    }
}
