<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

interface PlayersFactory
{
    public function createEmpty(): Players;
}
