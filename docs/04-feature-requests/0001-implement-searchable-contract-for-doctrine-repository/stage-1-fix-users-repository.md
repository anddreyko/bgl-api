# Stage 1: Add Alias to Doctrine Users Repository

## Stage Overview

### What This Stage Accomplishes

This stage adds the missing `getAlias()` method to the `Users` repository class. The `DoctrineRepository` abstract class
requires this method to be implemented by all concrete repositories, as it defines the entity alias used in DQL queries.

### Why It Needs to Be Done First

The `Users` repository is currently missing the `getAlias()` method, which is required by the
`DoctrineRepository::search()` method. This is a simple, quick fix that can be done first to prepare the production
repository for the new functionality.

### Dependencies

- None. This is the first stage.

---

## Implementation Steps

### Step 1: Open the Users repository file

File location: `src/Infrastructure/Persistence/Doctrine/Users.php`

### Step 2: Add the getAlias() method

Add the following method to the `Users` class:

```php
#[\Override]
public function getAlias(): string
{
    return 'u';
}
```

The alias 'u' follows the convention of using the first letter of the entity name (User → u).

### Step 3: Verify the change

Run lint check to ensure the syntax is correct:

```bash
composer lp:run
```

---

## Code References

### Current Users Repository
**File:** `src/Infrastructure/Persistence/Doctrine/Users.php:1-19`

```php
<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users as UserRepository;

/**
 * @extends DoctrineRepository<User>
 */
final class Users extends DoctrineRepository implements UserRepository
{
    #[\Override]
    public function getType(): string
    {
        return User::class;
    }
}
```

### Abstract Method Definition
**File:** `src/Infrastructure/Persistence/Doctrine/DoctrineRepository.php:30`

```php
abstract public function getAlias(): string;
```

### Example Implementation (TestDoctrineRepository)
**File:** `tests/Support/Repositories/TestDoctrineRepository.php:15-18`

```php
public function getAlias(): string
{
    return 'e';
}
```

---

## Files to Create/Modify

### MODIFY: `src/Infrastructure/Persistence/Doctrine/Users.php`

**Purpose:** Add the required `getAlias()` method for DQL query generation.

**Updated Implementation:**

```php
<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users as UserRepository;

/**
 * @extends DoctrineRepository<User>
 */
final class Users extends DoctrineRepository implements UserRepository
{
    #[\Override]
    public function getType(): string
    {
        return User::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'u';
    }
}
```

---

## Completion Criteria

### How to Verify This Stage is Done

1. **Method exists:** The `getAlias()` method is present in `Users.php`

2. **Returns correct alias:** Method returns `'u'`

3. **Has Override attribute:** Method has `#[\Override]` attribute

4. **Lint check passes:**
   ```bash
   composer lp:run
   ```

### Tests to Run

```bash
# Lint check
composer lp:run
```

### Expected Outcomes

- No syntax errors
- The `Users` repository is ready to use `search()` functionality

---

## Potential Issues

### Common Mistakes to Avoid

1. **Forgetting the `#[\Override]` attribute** - Required by project conventions for all overridden methods.

2. **Using wrong alias** - Convention is to use the first letter of the entity name for readability in DQL queries.

3. **Making the method non-public** - The method must be `public` to match the abstract declaration.
