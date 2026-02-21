<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Entities;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<User>
 */
interface Users extends Repository, Searchable
{
}
