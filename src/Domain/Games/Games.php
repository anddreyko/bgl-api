<?php

declare(strict_types=1);

namespace Bgl\Domain\Games;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<Game>
 */
interface Games extends Repository, Searchable
{
    public function findByBggId(int $bggId): ?Game;
}
