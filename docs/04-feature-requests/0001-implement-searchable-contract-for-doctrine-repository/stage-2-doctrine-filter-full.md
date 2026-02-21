# Stage 2: Create DoctrineFilter Class (Full Implementation)

## Stage Overview

### What This Stage Accomplishes

This stage creates the complete `DoctrineFilter` class that implements the `FilterVisitor` interface. The class
translates domain filter abstractions into Doctrine ORM query conditions. All seven visitor methods are implemented:
`all`, `equals`, `less`, `greater`, `and`, `or`, and `not`.

### Why It Needs to Be Done at This Point

The `DoctrineFilter` is the core component that enables the `DoctrineRepository::search()` method to work with filters.
This stage delivers the full implementation in one go, following the same pattern as the existing `InMemoryFilter`.

### Dependencies

- None. Can be done in parallel with Stage 1.

---

## Implementation Steps

### Step 1: Create the DoctrineFilter.php file

Create a new file at `src/Infrastructure/Persistence/Doctrine/DoctrineFilter.php`.

The class must:
1. Be `final readonly` to ensure immutability
2. Implement `FilterVisitor<string|Composite|null>` interface
3. Accept `QueryBuilder` and alias in constructor
4. Track parameter counter for unique parameter names

### Step 2: Implement the constructor and properties

```php
public function __construct(
    private QueryBuilder $qb,
    private string $alias,
) {
}
```

**Note:** Since the class is `readonly`, we use an array reference trick for the mutable counter.

### Step 3: Implement the resolve() method

This private method handles the differentiation between `Field` objects and scalar values:

- If `$value` is a `Field` instance → return qualified field name like `"e.fieldName"`
- Otherwise → generate unique parameter name, set the parameter on QueryBuilder, return `:paramName`

### Step 4: Implement all() method

The `All::Filter` should match all records, so no WHERE condition is needed. Return `null`.

### Step 5: Implement equals(), less(), greater() methods

Generate comparison conditions:
1. Resolve left operand using `resolve()`
2. Resolve right operand using `resolve()`
3. Build condition string with appropriate operator (`=`, `<`, `>`)
4. Return the condition string (do NOT call `andWhere()` here)

### Step 6: Implement and() method

For `AndX` filter:

1. Return `null` if filters array is empty
2. Collect conditions from each child filter by calling `accept($this)`
3. Filter out null conditions
4. Wrap in `$qb->expr()->andX(...$conditions)`
5. Return the composite expression

### Step 7: Implement or() method

Same pattern as `and()`, but using `$qb->expr()->orX()`.

### Step 8: Implement not() method

For `Not` filter:

1. Get inner condition by calling `accept($this)` on the wrapped filter
2. If inner condition is `null` (from `All`), return `'1 = 0'` to match no records
3. Otherwise, wrap in `NOT(...)` and return

---

## Code References

### FilterVisitor Interface (Must Implement)
**File:** `src/Core/Listing/FilterVisitor.php:1-43`

```php
interface FilterVisitor
{
    public function all(All $filter): mixed;
    public function equals(Equals $filter): mixed;
    public function less(Less $filter): mixed;
    public function greater(Greater $filter): mixed;
    public function and(AndX $filter): mixed;
    public function or(OrX $filter): mixed;
    public function not(Not $filter): mixed;
}
```

### Reference Implementation (InMemoryFilter)

**File:** `src/Infrastructure/Persistence/InMemory/InMemoryFilter.php:1-73`

Key patterns to follow:

- `resolve()` method for Field vs scalar handling
- Recursive `accept($this)` calls for composite filters
- Closure-based filtering (we use condition strings instead)

### Integration Point (DoctrineRepository)
**File:** `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php:49-51`

This is where `DoctrineFilter` is instantiated:
```php
$visitor = new DoctrineFilter($qb, $alias);
$filter->accept($visitor);
```

---

## Files to Create/Modify

### NEW: `src/Infrastructure/Persistence/Doctrine/DoctrineFilter.php`

**Purpose:** Translates Core filter abstractions into Doctrine ORM query conditions.

**Full Implementation:**

```php
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
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

/**
 * Transforms Core filter abstractions into Doctrine ORM query conditions.
 *
 * @implements FilterVisitor<string|Composite|null>
 * @see \Bgl\Tests\Integration\Repositories\DoctrineRepositoryCest
 */
final readonly class DoctrineFilter implements FilterVisitor
{
    /** @var array{counter: int} */
    private array $state;

    public function __construct(
        private QueryBuilder $qb,
        private string $alias,
    ) {
        $this->state = ['counter' => 0];
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
        if ($filter->filters === []) {
            return null;
        }

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
        if ($filter->filters === []) {
            return null;
        }

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

        $paramName = 'param_' . $this->state['counter']++;
        $this->qb->setParameter($paramName, $value);

        return ":{$paramName}";
    }
}
```

---

## Completion Criteria

### How to Verify This Stage is Done

1. **File exists:** `src/Infrastructure/Persistence/Doctrine/DoctrineFilter.php`

2. **Lint check passes:**
   ```bash
   composer lp:run
   ```

3. **Static analysis passes:**
   ```bash
   composer ps:run
   ```

4. **Class structure is correct:**
    - Class is `final readonly`
    - Implements `FilterVisitor` interface
    - All 7 methods implemented with `#[\Override]` attribute

5. **Method behaviors:**
    - `all()` returns `null`
    - `equals()` returns `"e.field = :param_0"`
    - `less()` returns `"e.field < :param_0"`
    - `greater()` returns `"e.field > :param_0"`
    - `and()` returns `Composite` expression or `null`
    - `or()` returns `Composite` expression or `null`
    - `not()` returns `"NOT(...)"` or `"1 = 0"`

### Tests to Run

```bash
# Lint check
composer lp:run

# Static analysis
composer ps:run
```

### Expected Outcomes

- File compiles without syntax errors
- No Psalm errors
- Class structure matches interface requirements

---

## Potential Issues

### Common Mistakes to Avoid

1. **Forgetting `declare(strict_types=1)`** - Required by project conventions.

2. **Not using `#[\Override]` attribute** - All interface method implementations must have this attribute.

3. **Incorrect parameter naming** - Parameters must be unique across the query. Use counter.

4. **Calling `andWhere()` in basic methods** - Do NOT call `andWhere()` here. The condition string is returned and
   applied in `DoctrineRepository::search()`.

5. **Wrong namespace** - Must be `Bgl\Infrastructure\Persistence\Doctrine`.

### Edge Cases

1. **Both operands are Fields** - Should generate `e.field1 = e.field2` (field-to-field comparison).

2. **Neither operand is a Field** - Should generate `:param_0 = :param_1` (literal comparison).

3. **Empty AndX/OrX** - Should return `null` (no condition added).

4. **None filter** - Creates `Not(All)`, which must produce `1 = 0`.

5. **Deeply nested filters** - Recursive `accept($this)` handles naturally.
