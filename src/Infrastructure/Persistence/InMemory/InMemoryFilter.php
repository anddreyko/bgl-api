<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Core\Listing\Fields\FieldAccessor;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\FilterVisitor;
use Bgl\Core\ValueObjects\DateTime;

/**
 * @implements FilterVisitor<\Closure(array|object): bool>
 * @see \Bgl\Tests\Integration\Repositories\InMemoryRepositoryCest
 */
final readonly class InMemoryFilter implements FilterVisitor
{
    public function __construct(
        private FieldAccessor $accessor = new AnyFieldAccessor(),
    ) {
    }

    #[\Override]
    public function all(All $filter): mixed
    {
        return static fn(): true => true;
    }

    #[\Override]
    public function equals(Equals $filter): mixed
    {
        return fn(array|object $entity): bool => $this->normalize($this->resolve($entity, $filter->left))
            === $this->normalize($this->resolve($entity, $filter->right));
    }

    #[\Override]
    public function less(Less $filter): mixed
    {
        return fn(array|object $entity): bool => $this->toComparable($this->resolve($entity, $filter->left))
            < $this->toComparable($this->resolve($entity, $filter->right));
    }

    #[\Override]
    public function greater(Greater $filter): mixed
    {
        return fn(array|object $entity): bool => $this->toComparable($this->resolve($entity, $filter->left))
            > $this->toComparable($this->resolve($entity, $filter->right));
    }

    #[\Override]
    public function and(AndX $filter): mixed
    {
        return fn(array|object $entity): bool => array_all(
            $filter->filters,
            fn($childFilter) => $childFilter->accept($this)($entity)
        );
    }

    #[\Override]
    public function or(OrX $filter): mixed
    {
        return fn(array|object $entity): bool => array_any(
            $filter->filters,
            fn($childFilter) => $childFilter->accept($this)($entity)
        );
    }

    #[\Override]
    public function not(Not $filter): mixed
    {
        return fn(array|object $entity): bool => !$filter->filter->accept($this)($entity);
    }

    #[\Override]
    public function contains(Contains $filter): mixed
    {
        return fn(array|object $entity): bool => str_contains(
            mb_strtolower((string)$this->resolve($entity, $filter->left)),
            mb_strtolower((string)$filter->right)
        );
    }

    private function resolve(array|object $entity, mixed $value): mixed
    {
        if ($value instanceof Field) {
            return $this->accessor->get($entity, $value->field);
        }

        return $value;
    }

    /**
     * Normalize Stringable value objects to string for strict equality.
     */
    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \Stringable) {
            return (string)$value;
        }

        return $value;
    }

    /**
     * Convert date/time value objects to timestamps for ordering comparisons.
     * Non-date values pass through unchanged for native PHP comparison.
     */
    private function toComparable(mixed $value): mixed
    {
        if ($value instanceof DateTime) {
            return $value->getNullableValue()?->getTimestamp();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        return $value;
    }
}
