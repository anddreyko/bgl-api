<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\FilterVisitor;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements FilterVisitor<\Closure(array|object): bool>
 */
final class DoctrineFilter implements FilterVisitor
{
    private array $parameters = [];
    private int $paramCounter = 0;

    public function __construct(private readonly QueryBuilder $qb, private readonly string $alias = 'e')
    {
    }

    public function all(All $filter): QueryBuilder
    {
        return $this->qb;
    }

    public function equals(Equals $filter): QueryBuilder
    {
        $condition = $this->buildComparisonCondition($filter->left, $filter->right, '=');
        $this->qb->andWhere($condition);
        $this->applyParameters();

        return $this->qb;
    }

    public function less(Less $filter): QueryBuilder
    {
        $condition = $this->buildComparisonCondition($filter->left, $filter->right, '<');
        $this->qb->andWhere($condition);
        $this->applyParameters();

        return $this->qb;
    }

    public function greater(Greater $filter): QueryBuilder
    {
        $condition = $this->buildComparisonCondition($filter->left, $filter->right, '>');
        $this->qb->andWhere($condition);
        $this->applyParameters();

        return $this->qb;
    }

    public function and(AndX $filter): QueryBuilder
    {
        $conditions = [];

        foreach ($filter->filters as $childFilter) {
            $conditions[] = $this->extractCondition($childFilter);
        }

        if (count($conditions) > 0) {
            $expr = $this->qb->expr()->andX(...$conditions);
            $this->qb->andWhere($expr);
            $this->applyParameters();
        }

        return $this->qb;
    }

    public function or(OrX $filter): QueryBuilder
    {
        $conditions = [];

        foreach ($filter->filters as $childFilter) {
            $conditions[] = $this->extractCondition($childFilter);
        }

        if (count($conditions) > 0) {
            $expr = $this->qb->expr()->orX(...$conditions);
            $this->qb->andWhere($expr);
            $this->applyParameters();
        }

        return $this->qb;
    }

    public function not(Not $filter): QueryBuilder
    {
        $condition = $this->extractCondition($filter->filter);
        $this->qb->andWhere($this->qb->expr()->not($condition));
        $this->applyParameters();

        return $this->qb;
    }

    private function extractCondition(Filter $filter): Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx
    {
        $subQb = clone $this->qb;
        $subQb->resetDQLParts(['where']);

        $visitor = new self($subQb, $this->alias);
        $filter->accept($visitor);

        // Собираем параметры из вложенного посетителя
        $this->parameters = array_merge($this->parameters, $visitor->parameters);

        $wherePart = $subQb->getDQLPart('where');

        return $wherePart ?: $this->qb->expr()->andX(); // Возвращаем пустое условие, если нет where
    }

    private function buildComparisonCondition(mixed $left, mixed $right, string $operator): Expr\Comparison
    {
        $leftExpr = $this->resolveExpression($left);
        $rightExpr = $this->resolveExpression($right);

        if (is_array($leftExpr)) {
            [$leftDql, $leftParam] = $leftExpr;
            $paramName = $this->generateParamName();
            $this->parameters[$paramName] = $leftParam;

            return new Expr\Comparison(":{$paramName}", $operator, $rightExpr);
        }

        if (is_array($rightExpr)) {
            [$rightDql, $rightParam] = $rightExpr;
            $paramName = $this->generateParamName();
            $this->parameters[$paramName] = $rightParam;

            return new Expr\Comparison($leftExpr, $operator, ":{$paramName}");
        }

        return new Expr\Comparison($leftExpr, $operator, $rightExpr);
    }

    private function resolveExpression(mixed $value): string|array
    {
        if ($value instanceof Field) {
            return "{$this->alias}.{$value->field}";
        }

        return ['?', $value];
    }

    private function applyParameters(): void
    {
        foreach ($this->parameters as $name => $value) {
            $this->qb->setParameter($name, $value);
        }
        $this->parameters = [];
    }

    private function generateParamName(): string
    {
        return 'param_' . ++$this->paramCounter;
    }
}
