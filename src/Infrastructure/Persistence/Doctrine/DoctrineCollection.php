<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection as DoctrineArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollectionInterface;

/**
 * Adapts Doctrine Collection to Core Collection interface.
 *
 * @template T of object
 * @implements Collection<T>
 */
final readonly class DoctrineCollection implements Collection
{
    /**
     * @param DoctrineCollectionInterface<int, T> $inner
     */
    public function __construct(private DoctrineCollectionInterface $inner = new DoctrineArrayCollection())
    {
    }

    #[\Override]
    public function add(mixed $element): void
    {
        $this->inner->add($element);
    }

    #[\Override]
    public function toArray(): array
    {
        return $this->inner->toArray();
    }

    #[\Override]
    public function count(): int
    {
        return $this->inner->count();
    }

    /**
     * @return DoctrineCollectionInterface<int, T>
     */
    public function unwrap(): DoctrineCollectionInterface
    {
        return $this->inner;
    }
}
