<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Player;

interface PlayersFactory
{
    public function createEmpty(): Players;
}
