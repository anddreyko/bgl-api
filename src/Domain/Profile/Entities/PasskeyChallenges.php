<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<PasskeyChallenge>
 */
interface PasskeyChallenges extends Repository, Searchable
{
    public function findByChallenge(string $challenge): ?PasskeyChallenge;
}
