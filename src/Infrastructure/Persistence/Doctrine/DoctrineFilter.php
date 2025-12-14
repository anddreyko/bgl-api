<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\FilterVisitor;
use Doctrine\ORM\Query\Expr\Andx as DoctrineAndx;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Orx as DoctrineOrx;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements FilterVisitor<string|Composite|null>
 * @see \Bgl\Tests\Integration\Repositories\DoctrineRepositoryCest
 */
final class DoctrineFilter implements FilterVisitor
{
    private int $counter = 0;

    public function __construct(
        private readonly QueryBuilder $qb,
        private readonly string $alias,
    ) {
    }

    #[\Override]
    public function all(All $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function equals(Equals $filter): mixed
    {
        $left = $this->resolve($filter->left);
        $right = $this->resolve($filter->right);

        return "{$left} = {$right}";
    }

    #[\Override]
    public function less(Less $filter): mixed
    {
        $left = $this->resolve($filter->left);
        $right = $this->resolve($filter->right);

        return "{$left} < {$right}";
    }

    #[\Override]
    public function greater(Greater $filter): mixed
    {
        $left = $this->resolve($filter->left);
        $right = $this->resolve($filter->right);

        return "{$left} > {$right}";
    }

    #[\Override]
    public function and(AndX $filter): mixed
    {
        /** @var list<DoctrineAndx|DoctrineOrx|string> $conditions */
        $conditions = array_filter(
            array_map(fn($f) => $f->accept($this), $filter->filters),
            static fn($c) => $c !== null
        );

        if ($conditions === []) {
            return null;
        }

        return $this->qb->expr()->andX(...$conditions);
    }

    #[\Override]
    public function or(OrX $filter): mixed
    {
        /** @var list<DoctrineAndx|DoctrineOrx|string> $conditions */
        $conditions = array_filter(
            array_map(fn($f) => $f->accept($this), $filter->filters),
            static fn($c) => $c !== null
        );

        if ($conditions === []) {
            return null;
        }

        return $this->qb->expr()->orX(...$conditions);
    }

    #[\Override]
    public function not(Not $filter): mixed
    {
        $innerCondition = $filter->filter->accept($this);

        if ($innerCondition === null) {
            // NOT(All) should match no records
            return '1 = 0';
        }

        return "NOT({$innerCondition})";
    }

    private function resolve(mixed $value): string
    {
        if ($value instanceof Field) {
            return "{$this->alias}.{$value->field}";
        }

        $paramName = 'param_' . $this->counter++;
        $this->qb->setParameter($paramName, $value);

        return ":{$paramName}";
    }
}
