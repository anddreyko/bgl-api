<?php

declare(strict_types=1);

namespace Bgl\Domain\Mates;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;
use Bgl\Core\ValueObjects\Uuid;

/**
 * @extends Repository<Mate>
 */
interface Mates extends Repository, Searchable
{
    public function findByUserAndName(Uuid $userId, string $name): ?Mate;

    /**
     * @return list<Mate>
     */
    public function findAllByUser(
        Uuid $userId,
        int $limit,
        int $offset,
        string $sortField = 'name',
        string $sortDir = 'asc',
    ): array;

    public function countByUser(Uuid $userId): int;

    /**
     * @return list<Mate>
     */
    public function findSystemMates(): array;
}
