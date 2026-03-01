<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Player;

/**
 * Null-object for Players collection.
 * Used as default initializer; overridden by create() or Doctrine hydration.
 */
final class EmptyPlayers implements Players
{
    #[\Override]
    public function add(object $entity): void
    {
    }

    #[\Override]
    public function remove(object $entity): void
    {
    }

    #[\Override]
    public function find(string $id): ?object
    {
        return null;
    }

    #[\Override]
    public function count(): int
    {
        return 0;
    }

    #[\Override]
    public function getIterator(): \EmptyIterator
    {
        return new \EmptyIterator();
    }
}
