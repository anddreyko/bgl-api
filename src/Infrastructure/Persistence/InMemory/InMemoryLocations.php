<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations;

/**
 * @extends InMemoryRepository<Location>
 */
final class InMemoryLocations extends InMemoryRepository implements Locations
{
    #[\Override]
    public function findByUserAndName(Uuid $userId, string $name): ?Location
    {
        $lowerName = mb_strtolower($name);
        foreach ($this->getEntities() as $location) {
            if (
                $location->getUserId()->getValue() === $userId->getValue()
                && mb_strtolower($location->getName()) === $lowerName
                && !$location->isDeleted()
            ) {
                return $location;
            }
        }

        return null;
    }

    #[\Override]
    public function findAllByUser(
        Uuid $userId,
        int $limit,
        int $offset,
        string $sortField = 'name',
        string $sortDir = 'asc',
    ): array {
        $matches = [];
        foreach ($this->getEntities() as $location) {
            if ($location->getUserId()->getValue() === $userId->getValue() && !$location->isDeleted()) {
                $matches[] = $location;
            }
        }

        usort($matches, static function (Location $a, Location $b) use ($sortField, $sortDir): int {
            $aVal = $sortField === 'createdAt' ? $a->getCreatedAt()->getValue()->getTimestamp() : $a->getName();
            $bVal = $sortField === 'createdAt' ? $b->getCreatedAt()->getValue()->getTimestamp() : $b->getName();
            $cmp = $aVal <=> $bVal;

            return strtolower($sortDir) === 'desc' ? -$cmp : $cmp;
        });

        return \array_slice($matches, $offset, $limit);
    }

    #[\Override]
    public function countByUser(Uuid $userId): int
    {
        $count = 0;
        foreach ($this->getEntities() as $location) {
            if ($location->getUserId()->getValue() === $userId->getValue() && !$location->isDeleted()) {
                ++$count;
            }
        }

        return $count;
    }
}
