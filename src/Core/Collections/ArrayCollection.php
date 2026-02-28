<?php

declare(strict_types=1);

namespace Bgl\Core\Collections;

/**
 * @template T of object
 * @implements Collection<T>
 */
final class ArrayCollection implements Collection
{
    /** @var list<T> */
    private array $items = [];

    #[\Override]
    public function add(mixed $element): void
    {
        $this->items[] = $element;
    }

    #[\Override]
    public function toArray(): array
    {
        return $this->items;
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->items);
    }
}
