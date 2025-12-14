# Stage 4: Integration Testing

## Stage Overview

### What This Stage Accomplishes

This stage validates the entire `DoctrineFilter` implementation by running the existing integration test suite. The
`BaseRepository` test class provides comprehensive scenarios that test all filter types, pagination, and sorting.
Successfully passing these tests confirms that the Doctrine implementation behaves consistently with the InMemory
reference implementation.

### Why It Needs to Be Done at This Point

Integration testing must follow implementation because it verifies that all components work together correctly. The
existing `DoctrineRepositoryCest` test class extends `BaseRepository`, which contains all the test scenarios. Running
these tests will immediately reveal any issues with the implementation.

### Dependencies

- **Stage 1** completed (Users repository has `getAlias()`)
- **Stage 2** completed (DoctrineFilter is fully implemented)
- **Stage 3** completed (DoctrineRepository::search() applies conditions)

---

## Implementation Steps

### Step 1: Run the Doctrine Repository Integration Tests

Execute the specific test file:

```bash
make t tests/Integration/Repositories/DoctrineRepositoryCest.php
```

### Step 2: Analyze Test Results

If tests fail, identify which specific test methods are failing:

1. **testQueryDefaultCall** - Tests `All::Filter` returns all entities
2. **testFilter** - Tests various filter scenarios (None, Equals, Greater, Less, AndX, OrX)
3. **testSort** - Tests single-field sorting
4. **testMultiSort** - Tests multi-field sorting
5. **testOffsetLimit** - Tests pagination
6. **testAdd** - Tests entity persistence
7. **testRemove** - Tests entity removal

### Step 3: Debug and Fix Common Issues

**If testFilter fails with None::Filter:**
- Ensure `not(Not $filter)` returns `'1 = 0'` when inner condition is `All`
- The `None::Filter` enum value creates `Not(All)`, which should match no records

**If testFilter fails with Equals:**
- Check parameter binding is working
- Verify the condition string format: `e.field = :param_0`
- Ensure `DoctrineRepository::search()` applies the returned condition

**If testFilter fails with Greater/Less:**
- Same checks as Equals, but with `>` and `<` operators

**If testFilter fails with AndX/OrX:**
- Ensure composite expressions are being built correctly
- Verify nested filters are being processed recursively

### Step 4: Verify Parameter Binding

If queries return unexpected results, add debug output to `DoctrineRepository::search()`:

```php
// Temporary debug (remove after debugging)
var_dump($qb->getDQL());
var_dump($qb->getParameters()->toArray());
```

### Step 5: Run All Integration Tests

After fixing any issues, run the full integration test suite:

```bash
make t-intg
```

---

## Code References

### Test Scenarios (BaseRepository)
**File:** `tests/Integration/Repositories/BaseRepository.php:1-149`

Key test data providers:

**providerFilter (lines 75-98):**
```php
// None::Filter → expects empty array
// Equals(Field('id'), '1') → expects single match
// Equals('c', Field('value')) → expects multiple matches
// Greater(Field('id'), '2') → expects entities with id > 2
// Less(Field('value'), 'c') → expects entities with value < 'c'
// AndX with conflicting conditions → expects empty
// OrX with two conditions → expects union
```

**Test data setup (lines 66-71):**
```php
new TestEntity('1', 'a'),
new TestEntity('2', 'b'),
new TestEntity('3', 'c'),
new TestEntity('4', 'c'),
```

### DoctrineRepositoryCest
**File:** `tests/Integration/Repositories/DoctrineRepositoryCest.php:1-31`

The test class uses Doctrine's EntityManager and the `TestDoctrineRepository`.

---

## Files to Create/Modify

No new files in this stage. This stage is about running tests and fixing issues.

### Potential Modifications (If Tests Fail)

1. **DoctrineFilter.php** - Fix any bugs in filter logic
2. **DoctrineRepository.php** - Fix condition application if needed

---

## Completion Criteria

### How to Verify This Stage is Done

1. **All DoctrineRepositoryCest tests pass:**
   ```bash
   make t tests/Integration/Repositories/DoctrineRepositoryCest.php
   ```

   Expected output: All tests pass (green)

2. **All integration tests pass:**
   ```bash
   make t-intg
   ```

3. **Test parity with InMemory:**
   ```bash
   make t tests/Integration/Repositories/InMemoryRepositoryCest.php
   ```

   Both Doctrine and InMemory tests should produce the same results.

### Tests to Run

```bash
# Specific Doctrine tests
make t tests/Integration/Repositories/DoctrineRepositoryCest.php

# All integration tests
make t-intg

# Verify InMemory still works (sanity check)
make t tests/Integration/Repositories/InMemoryRepositoryCest.php
```

### Expected Outcomes

- All `DoctrineRepositoryCest` tests pass
- Test behavior matches `InMemoryRepositoryCest`
- No regressions in other integration tests

---

## Potential Issues

### Common Test Failures and Solutions

**Failure: testFilter with None::Filter doesn't return empty**

```
Expected: []
Actual: [TestEntity('1', 'a'), TestEntity('2', 'b'), ...]
```

**Solution:** Ensure `not()` method returns `'1 = 0'` when inner filter returns `null` (which `All` does).

---

**Failure: testFilter with Equals returns wrong results**

```
Expected: [TestEntity('1', 'a')]
Actual: []
```

**Solution:**

1. Check that `DoctrineRepository::search()` calls `$qb->andWhere($condition)` with the returned value
2. Verify parameter binding with `$qb->getParameters()`

---

**Failure: testFilter with OrX returns unexpected results**

**Solution:** Check that `orX()` method builds the composite expression correctly and that child conditions are being
collected.

---

**Failure: Database connection errors**

**Solution:** Ensure the test database is running and migrations are up to date:
```bash
make migrate
```

### Debugging Tips

1. **Print the DQL:**
   ```php
   echo $qb->getDQL();
   ```

2. **Print parameters:**
   ```php
   print_r($qb->getParameters()->toArray());
   ```

3. **Print the SQL:**
   ```php
   echo $qb->getQuery()->getSQL();
   ```
