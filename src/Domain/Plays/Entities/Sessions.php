<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<Session>
 */
interface Sessions extends Repository, Searchable
{
}
