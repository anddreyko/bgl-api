<?php

declare(strict_types=1);

namespace Bgl\Domain\Locations;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;
use Bgl\Core\ValueObjects\Uuid;

/**
 * @extends Repository<Location>
 */
interface Locations extends Repository, Searchable
{
    public function findByUserAndName(Uuid $userId, string $name): ?Location;

    /**
     * @return list<Location>
     */
    public function findAllByUser(
        Uuid $userId,
        int $limit,
        int $offset,
        string $sortField = 'name',
        string $sortDir = 'asc',
    ): array;

    public function countByUser(Uuid $userId): int;
}
