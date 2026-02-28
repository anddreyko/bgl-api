<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Bridges Core Collection and Doctrine Collection.
 * Extends Doctrine ArrayCollection (ORM-compatible) and implements Core Collection (Domain-compatible).
 *
 * @template T of object
 * @extends ArrayCollection<int, T>
 * @implements Collection<T>
 */
final class DoctrineArrayCollection extends ArrayCollection implements Collection
{
    #[\Override]
    public function add(mixed $element): void
    {
        /** @var T $element */
        parent::add($element);
    }
}
