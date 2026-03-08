# MATES-004: System Mates (Anonymous, Automa) and self-as-mate

## Scope

1. System Mates: global mates without user_id owner (Anonymous Player, Automa). Available to all users.
2. Self-as-mate: user automatically becomes a mate of themselves (for tracking self as player in sessions).

## Design Decisions

- `Mate.userId` becomes nullable. NULL = system mate (global, shared by all users).
- System mates are seeded via migration (fixed UUIDs for idempotency).
- Self-as-mate: created on first need or via migration for existing users. Mate with `userId = user's id` and `name = user's name`.
- `validatePlayers()` in handlers: allow system mates (userId=null) for any user.
- Self-mate is owned by user (userId matches), so existing validation works.

## Changes

### Domain Layer

- [ ] `src/Domain/Mates/Mate.php`: make `userId` nullable (`?Uuid`), add `static createSystem()` factory
- [ ] `src/Domain/Mates/Mate.php`: add `isSystem(): bool` method (returns `userId === null`)
- [ ] `src/Domain/Mates/Mates.php` (interface): add `findSystemMates(): array` method

### Infrastructure Layer

- [ ] `MateMapping.php`: make `user_id` column nullable
- [ ] `DoctrineMates.php`: implement `findSystemMates()`
- [ ] `InMemoryMates.php`: implement `findSystemMates()`
- [ ] Generate Doctrine migration via `make migrate-gen`
- [ ] Migration: seed Anonymous (fixed UUID) and Automa (fixed UUID) system mates

### Application Layer

- [ ] `CreatePlay/Handler.php` `validatePlayers()`: allow system mates (mate.userId === null) without ownership check
- [ ] `UpdatePlay/Handler.php` `validatePlayers()`: same change
- [ ] `ListMates/Handler.php`: include system mates in response (or separate endpoint)
- [ ] Handlers that list/filter mates: include system mates for any user

### Presentation Layer

- [ ] OpenAPI mates response: add `is_system` field to mate schema

### Self-as-mate

- [ ] Migration: INSERT mate for existing user (79bbd64d... production, 521855a8... local) with user's name
- [ ] `CreateMate` or registration flow: auto-create self-mate when user registers (future, out of scope for now)

### Tests

- [ ] Unit test: Mate::createSystem(), isSystem()
- [ ] Functional test: create play with system mate (Anonymous), verify no ownership error
- [ ] Functional test: list mates includes system mates

### Quality Gates

- [ ] `make scan` passes
- [ ] Migration generates cleanly, seeds system mates
