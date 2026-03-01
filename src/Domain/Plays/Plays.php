<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<Play>
 */
interface Plays extends Repository, Searchable
{
}
