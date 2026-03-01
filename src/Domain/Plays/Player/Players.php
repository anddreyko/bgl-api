<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Player;

use Bgl\Core\Collections\Repository;

/**
 * @extends Repository<Player>
 * @extends \IteratorAggregate<int, Player>
 */
interface Players extends Repository, \IteratorAggregate
{
}
