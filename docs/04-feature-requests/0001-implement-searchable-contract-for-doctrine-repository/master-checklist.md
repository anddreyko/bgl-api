# Feature Implementation: DoctrineFilter for Searchable Contract

**Overall Progress:** 5 of 5 stages completed

**Feature:** Implement `DoctrineFilter` class that translates core domain filter abstractions into Doctrine ORM query
conditions.

**Estimated Total Time:** ~2-3 hours

---

## Stage 1: Add Alias to Doctrine Users Repository (~15min) - DONE
**Dependencies:** None

- [x] Add `getAlias()` method to `src/Infrastructure/Persistence/Doctrine/Users.php`
- [x] Return 'u' as the alias for User entity
- [x] Add `#[\Override]` attribute
- [x] Verify file passes `make lp`

Details: [stage-1-fix-users-repository.md](./stage-1-fix-users-repository.md)

---

## Stage 2: Create DoctrineFilter Class (Full Implementation) (~60min) - DONE

**Dependencies:** None (can be done in parallel with Stage 1)

- [x] Create `DoctrineFilter.php` in `src/Infrastructure/Persistence/Doctrine/`
- [x] Implement class structure with constructor
- [x] Implement `resolve()` private method for Field vs scalar handling
- [x] Implement `all()` method (returns null)
- [x] Implement `equals()` method with parameter binding
- [x] Implement `less()` method with parameter binding
- [x] Implement `greater()` method with parameter binding
- [x] Implement `and()` method for AndX filter
- [x] Implement `or()` method for OrX filter
- [x] Implement `not()` method for Not filter
- [x] Handle empty filter arrays edge case
- [x] Verify file passes `make lp` and `make ps`

Details: [stage-2-doctrine-filter-full.md](./stage-2-doctrine-filter-full.md)

---

## Stage 3: Fix Search Method in DoctrineRepository (~20min) - DONE

**Dependencies:** Stage 2 completed

- [x] Update `search()` method in `DoctrineRepository.php`
- [x] Capture return value from `$filter->accept($visitor)`
- [x] Apply condition to QueryBuilder only if not null
- [x] Verify file passes `make lp` and `make ps`

Details: [stage-3-fix-doctrine-repository.md](./stage-3-fix-doctrine-repository.md)

---

## Stage 4: Integration Testing (~45min) - DONE
**Dependencies:** Stages 1, 2, 3 completed

- [x] Run `make t tests/Integration/Repositories/DoctrineRepositoryCest.php`
- [x] Fix any failing tests
- [x] Verify all filter types work correctly (None, Equals, Greater, Less, AndX, OrX)
- [x] Verify pagination works correctly
- [x] Verify sorting works correctly
- [x] Run `make t-intg` for all integration tests

Details: [stage-4-integration-testing.md](./stage-4-integration-testing.md)

---

## Stage 5: Final Validation and Cleanup (~30min) - DONE
**Dependencies:** All previous stages completed

- [x] Run `make scan` (mandatory before push) - Note: Pre-existing issue with unused `lcobucci/jwt` dependency
- [x] Fix any Psalm errors in DoctrineFilter and DoctrineRepository
- [x] Run `make dt` (architecture tests) - 0 violations
- [x] Review code for simplification opportunities

Details: [stage-5-final-validation.md](./stage-5-final-validation.md)

---

## Quick Reference Commands

```bash
# Lint check
make lp

# Static analysis
make ps

# Run specific test file
make t tests/Integration/Repositories/DoctrineRepositoryCest.php

# Run all integration tests
make t-intg

# Full validation (MANDATORY before push)
make scan

# Architecture tests
make dt
```

## Files to Create/Modify

| File                                                             | Action | Stage |
|------------------------------------------------------------------|--------|-------|
| `src/Infrastructure/Persistence/Doctrine/Users.php`              | MODIFY | 1     |
| `src/Infrastructure/Persistence/Doctrine/DoctrineFilter.php`     | CREATE | 2     |
| `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php` | MODIFY | 3     |
