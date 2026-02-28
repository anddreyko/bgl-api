# Stage 2: Fix User Entity Bug [P]

## Overview

Fix the non-deterministic `getName()` method and remove `generateDefaultName()` from User entity. Name generation responsibility moves to the Application layer (Register Handler in Stage 4).

## Dependencies

None -- can run in parallel with Stage 1.

## Implementation Steps

### 2.1 Remove generateDefaultName()

File: `src/Domain/Profile/Entities/User.php` (lines 40-43)

Delete the private static method `generateDefaultName()`.

### 2.2 Fix getName()

File: `src/Domain/Profile/Entities/User.php` (lines 79-82)

Current (buggy):
```php
public function getName(): string
{
    return $this->name ?? self::generateDefaultName();
}
```

Fixed:
```php
public function getName(): string
{
    return $this->name ?? 'Player';
}
```

Fallback `'Player'` handles legacy DB rows where `name` is null.

### 2.3 Update register() factory method

File: `src/Domain/Profile/Entities/User.php` (line 36)

Current:
```php
name: $name ?? self::generateDefaultName(),
```

Changed -- make `$name` required (non-null):
```php
name: $name,
```

Update method signature: `?string $name = null` -> `string $name`

This enforces that the caller (Handler) always provides a name.

## Files to Create/Modify

| File | Action |
|------|--------|
| `src/Domain/Profile/Entities/User.php` | MODIFY |

## Completion Criteria

- `generateDefaultName()` removed from User entity
- `getName()` returns stable value (no random generation)
- `register()` requires explicit `$name` parameter

## Verification

```bash
composer lp:run
composer ps:run src/Domain/Profile/Entities/User.php
```

## Potential Issues

- Existing code calling `User::register()` without name will fail at compile time -- Stage 4 fixes this in Register Handler
- Existing tests creating User without name -- will need update in Stage 5
