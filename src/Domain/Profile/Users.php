<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<User>
 */
interface Users extends Repository, Searchable
{
    public function findByEmail(string $email): ?User;

    public function findByName(string $name): ?User;
}
