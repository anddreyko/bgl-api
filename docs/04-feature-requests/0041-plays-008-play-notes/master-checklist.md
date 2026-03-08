# PLAYS-008: Add notes to Play session

## Scope

Add optional `notes` (text) field to Play entity for storing session commentary.

## Changes

### Domain Layer

- [ ] `src/Domain/Plays/Play.php`: add `?string $notes` field, constructor param, getter, include in `update()` method

### Infrastructure Layer

- [ ] `PlayMapping.php`: add `notes` (text, nullable) field mapping
- [ ] Generate Doctrine migration via `make migrate-gen`

### Application Layer

- [ ] `CreatePlay/Command.php`: add `?string $notes = null`
- [ ] `CreatePlay/Handler.php`: pass `notes` to Play constructor
- [ ] `CreatePlay/Result.php`: add `notes` to output
- [ ] `UpdatePlay/Command.php`: add `?string $notes = null`
- [ ] `UpdatePlay/Handler.php`: pass `notes` to `update()`
- [ ] `UpdatePlay/Result.php`: add `notes` to output
- [ ] `GetPlay/Handler.php`: add `notes` to output
- [ ] `ListPlays/Handler.php`: add `notes` to output

### Presentation Layer

- [ ] `config/common/openapi/plays.php`: add `notes` to request/response schemas

### Tests

- [ ] Functional test: create play with notes, update play with notes
- [ ] Existing tests pass (field is nullable/optional)

### Quality Gates

- [ ] `make scan` passes
- [ ] Migration generates cleanly
