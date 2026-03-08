<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;

/**
 * @extends InMemoryRepository<Mate>
 */
final class InMemoryMates extends InMemoryRepository implements Mates
{
    #[\Override]
    public function findByUserAndName(Uuid $userId, string $name): ?Mate
    {
        $lowerName = mb_strtolower($name);
        foreach ($this->getEntities() as $mate) {
            if (
                $mate->getUserId() !== null
                && $mate->getUserId()->getValue() === $userId->getValue()
                && mb_strtolower($mate->getName()) === $lowerName
                && !$mate->isDeleted()
            ) {
                return $mate;
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
        foreach ($this->getEntities() as $mate) {
            if ($mate->getUserId() !== null && $mate->getUserId()->getValue() === $userId->getValue() && !$mate->isDeleted()) {
                $matches[] = $mate;
            }
        }

        usort($matches, static function (Mate $a, Mate $b) use ($sortField, $sortDir): int {
            $aVal = $sortField === 'createdAt' ? $a->getCreatedAt()->getValue()->getTimestamp() : $a->getName();
            $bVal = $sortField === 'createdAt' ? $b->getCreatedAt()->getValue()->getTimestamp() : $b->getName();
            $cmp = $aVal <=> $bVal;

            return strtolower($sortDir) === 'desc' ? -$cmp : $cmp;
        });

        return \array_slice($matches, $offset, $limit);
    }

    #[\Override]
    public function findSystemMates(): array
    {
        $result = [];
        foreach ($this->getEntities() as $mate) {
            if ($mate->getUserId() === null && !$mate->isDeleted()) {
                $result[] = $mate;
            }
        }

        return $result;
    }

    #[\Override]
    public function countByUser(Uuid $userId): int
    {
        $count = 0;
        foreach ($this->getEntities() as $mate) {
            if ($mate->getUserId() !== null && $mate->getUserId()->getValue() === $userId->getValue() && !$mate->isDeleted()) {
                ++$count;
            }
        }

        return $count;
    }
}
