# Stage 4: Integrate Generator into Registration

## Overview

Inject `Nomenclator` into `Register\Handler` so that registration without a name uses the thematic generator instead of `User#NNNN`.

## Dependencies

Stage 1 (Nomenclator exists), Stage 2 (User::register() requires explicit name).

## Implementation Steps

### 4.1 Add Nomenclator to Register\Handler constructor

File: `src/Application/Handlers/Auth/Register/Handler.php`

Add dependency:
```php
public function __construct(
    private Users $users,
    private Confirmer $confirmer,
    private Hasher $passwordHasher,
    private UuidGenerator $uuidGenerator,
    private ClockInterface $clock,
    private Nomenclator $nomenclator,  // NEW
) {}
```

### 4.2 Generate name when not provided

In `__invoke()`, before `User::register()`:

```php
$name = $command->name ?? $this->nomenclator->generate();

$user = User::register(
    id: $this->uuidGenerator->generate(),
    email: new Email($command->email),
    passwordHash: $passwordHash,
    createdAt: $now,
    name: $name,
);
```

## Files to Create/Modify

| File | Action |
|------|--------|
| `src/Application/Handlers/Auth/Register/Handler.php` | MODIFY |

## Completion Criteria

- Registration without name creates user with board-game-themed nickname
- Registration with name still uses the provided name
- Nomenclator is injected via DI (auto-wiring from persistence.php binding)

## Verification

```bash
composer lp:run
composer ps:run src/Application/Handlers/Auth/Register/Handler.php
```

## Potential Issues

- PHP-DI should auto-wire Nomenclator via the binding in persistence.php -- verify
