# Stage 3: Fix Search Method in DoctrineRepository

## Stage Overview

### What This Stage Accomplishes

This stage updates the `DoctrineRepository::search()` method to properly integrate with the `DoctrineFilter` visitor.
The key change is capturing the return value from `$filter->accept($visitor)` and applying it to the QueryBuilder only
if it's not null.

### Why It Needs to Be Done at This Point

The `DoctrineFilter` returns condition strings or composite expressions, but does NOT call `andWhere()` internally. The
repository's `search()` method must capture this return value and apply it to the QueryBuilder. Without this fix,
filters will not be applied to queries.

### Dependencies

- **Stage 2** completed (DoctrineFilter exists and is complete)

---

## Implementation Steps

### Step 1: Open DoctrineRepository.php

File location: `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php`

### Step 2: Locate the search() method

Find the current implementation around lines 44-76:

```php
#[\Override]
public function search(
    Filter $filter = None::Filter,
    PageSize $size = new PageSize(),
    PageNumber $number = new PageNumber(1),
    PageSort $sort = new PageSort([])
): iterable {
    $alias = $this->getAlias();
    $qb = $this->em->createQueryBuilder()
        ->select($alias)
        ->from($this->getType(), $alias);

    // Применяем фильтр
    $visitor = new DoctrineFilter($qb, $alias);
    $filter->accept($visitor);  // <-- This line needs to change

    // ... rest of the method
}
```

### Step 3: Update the filter application

Change lines 49-51 from:

```php
$visitor = new DoctrineFilter($qb, $alias);
$filter->accept($visitor);
```

To:

```php
$visitor = new DoctrineFilter($qb, $alias);
$condition = $filter->accept($visitor);
if ($condition !== null) {
    $qb->andWhere($condition);
}
```

### Step 4: Verify the change

Run lint and static analysis:

```bash
make lp
make ps
```

---

## Code References

### Current Implementation

**File:** `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php:44-76`

```php
#[\Override]
public function search(
    Filter $filter = None::Filter,
    PageSize $size = new PageSize(),
    PageNumber $number = new PageNumber(1),
    PageSort $sort = new PageSort([])
): iterable {
    $alias = $this->getAlias();
    $qb = $this->em->createQueryBuilder()
        ->select($alias)
        ->from($this->getType(), $alias);

    // Применяем фильтр
    $visitor = new DoctrineFilter($qb, $alias);
    $filter->accept($visitor);

    // Применяем сортировку
    foreach ($sort->fields as $field => $direction) {
        $order = $direction === SortDirection::Asc ? 'ASC' : 'DESC';
        $qb->addOrderBy("{$alias}.{$field}", $order);
    }

    // Применяем пагинацию
    $limit = $size->getValue();
    $offset = ($number->getValue() - 1) * $limit;

    if ($limit > 0) {
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        // Используем Paginator для корректного подсчета количества при использовании DQL с JOIN
        $paginator = new Paginator($qb->getQuery(), true);

        return iterator_to_array($paginator);
    }

    return $qb->getQuery()->getResult();
}
```

### DoctrineFilter Return Types

**File:** `src/Infrastructure/Persistence/Doctrine/DoctrineFilter.php`

The visitor methods return:

- `null` - from `all()` (no condition needed)
- `string` - from `equals()`, `less()`, `greater()`, `not()`
- `Composite` - from `and()`, `or()`

---

## Files to Create/Modify

### MODIFY: `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php`

**Purpose:** Integrate DoctrineFilter by applying returned conditions to QueryBuilder.

**Updated search() method (lines 44-76):**

```php
#[\Override]
public function search(
    Filter $filter = None::Filter,
    PageSize $size = new PageSize(),
    PageNumber $number = new PageNumber(1),
    PageSort $sort = new PageSort([])
): iterable {
    $alias = $this->getAlias();
    $qb = $this->em->createQueryBuilder()
        ->select($alias)
        ->from($this->getType(), $alias);

    // Применяем фильтр
    $visitor = new DoctrineFilter($qb, $alias);
    $condition = $filter->accept($visitor);
    if ($condition !== null) {
        $qb->andWhere($condition);
    }

    // Применяем сортировку
    foreach ($sort->fields as $field => $direction) {
        $order = $direction === SortDirection::Asc ? 'ASC' : 'DESC';
        $qb->addOrderBy("{$alias}.{$field}", $order);
    }

    // Применяем пагинацию
    $limit = $size->getValue();
    $offset = ($number->getValue() - 1) * $limit;

    if ($limit > 0) {
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        // Используем Paginator для корректного подсчета количества при использовании DQL с JOIN
        $paginator = new Paginator($qb->getQuery(), true);

        return iterator_to_array($paginator);
    }

    return $qb->getQuery()->getResult();
}
```

**Key change (lines 55-58):**

```php
// Before:
$visitor = new DoctrineFilter($qb, $alias);
$filter->accept($visitor);

// After:
$visitor = new DoctrineFilter($qb, $alias);
$condition = $filter->accept($visitor);
if ($condition !== null) {
    $qb->andWhere($condition);
}
```

---

## Completion Criteria

### How to Verify This Stage is Done

1. **Code updated:** The `search()` method captures and applies the filter condition.

2. **Lint check passes:**
   ```bash
   make lp
   ```

3. **Static analysis passes:**
   ```bash
   make ps
   ```

4. **Logic verification:**
    - `All::Filter` returns `null` → no WHERE clause added
    - `Equals(...)` returns condition string → `andWhere()` called
    - `None::Filter` returns `"1 = 0"` → query matches nothing

### Tests to Run

```bash
# Lint check
make lp

# Static analysis
make ps
```

### Expected Outcomes

- Code compiles without errors
- Filter conditions are properly applied to queries
- `All::Filter` returns all entities
- `None::Filter` returns empty result

---

## Potential Issues

### Common Mistakes to Avoid

1. **Forgetting the null check** - `All::Filter` returns `null`, and calling `$qb->andWhere(null)` would cause an error.

2. **Not capturing return value** - The original code didn't capture `$filter->accept($visitor)`, so conditions were
   never applied.

3. **Using wrong comparison** - Use `!== null` (strict comparison) for the check.

### Edge Cases

1. **All filter** - Returns `null`, no WHERE clause should be added.

2. **None filter** - Returns `"1 = 0"`, which should be added to match no records.

3. **Composite filter** - Returns `Composite` object, which `andWhere()` accepts.

### Debugging Tips

If filters don't work:

1. Print the DQL: `var_dump($qb->getDQL());`
2. Print parameters: `var_dump($qb->getParameters()->toArray());`
3. Check that `$condition` is not `null` when you expect a filter
