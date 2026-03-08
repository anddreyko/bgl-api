# Master Checklist: GetUser resolve by name

## Stage 1: Domain + Infrastructure

- [x] Add `findByName(string $name): ?User` to `Users` interface
- [x] Implement `findByName()` in Doctrine Users repository
- [x] Implement `findByName()` in InMemory Users repository

## Stage 2: Application handler

- [x] Add `UUID_PATTERN` constant and `resolveUser()` private method to `GetUser\Handler`
- [x] Change resolution: UUID → `find()`, otherwise → `findByName()`

## Stage 3: OpenAPI

- [x] Update `config/common/openapi/user.php` parameter description
- [x] Verified Query class has no `#[ValidUuid]` on userId field

## Stage 4: Tests (TDD)

- [x] Functional test: find user by name — success
- [x] Functional test: find user by nonexistent name — 404

## Stage 5: Quality gates

- [x] `composer test:func -- --group=user-info` green (4 tests, 9 assertions)
- [x] `make scan` passes (EXIT_CODE: 0)
