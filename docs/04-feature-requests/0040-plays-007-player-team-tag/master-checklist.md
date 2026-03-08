# PLAYS-007: Add team_tag and number to Player

## Scope

Add two optional fields to Player entity: `teamTag` (string, identifies team membership) and `number` (int, player order/position).

## Changes

### Domain Layer

- [ ] `src/Domain/Plays/Player/Player.php`: add `?string $teamTag` and `?int $number` to constructor, `create()`, getters
- [ ] Validation: `teamTag` max 50 chars, `number` >= 0 if provided

### Infrastructure Layer

- [ ] `PlayerMapping.php`: add `team_tag` (varchar 50, nullable) and `number` (integer, nullable) field mappings
- [ ] Generate Doctrine migration via `make migrate-gen`

### Application Layer

- [ ] `CreatePlay/Command.php`: extend player array type with `team_tag?: ?string, number?: ?int`
- [ ] `CreatePlay/Handler.php`: pass `teamTag` and `number` to `Player::create()`
- [ ] `CreatePlay/Result.php`: add `team_tag` and `number` to player output
- [ ] `UpdatePlay/Command.php`: same as CreatePlay
- [ ] `UpdatePlay/Handler.php`: same as CreatePlay
- [ ] `UpdatePlay/Result.php`: same as CreatePlay
- [ ] `GetPlay/Handler.php`: add `team_tag` and `number` to player output
- [ ] `ListPlays/Handler.php`: add `team_tag` and `number` to player output

### Presentation Layer

- [ ] `config/common/openapi/plays.php`: add `team_tag` and `number` to player schema (create, update, response)

### Tests

- [ ] Unit test: Player::create() with teamTag and number, validation edge cases
- [ ] Functional test: create play with team_tag/number, update play with team_tag/number
- [ ] Existing tests pass without changes (fields are nullable/optional)

### Quality Gates

- [ ] `make scan` passes
- [ ] Migration generates cleanly
