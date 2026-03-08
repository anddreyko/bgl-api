# LOCATIONS-001: Create Location entity as separate bounded context

## Scope

New BC `Locations` (analogous to Mates). CRUD for locations where board game sessions happen.
Play gets optional `locationId` reference.

## Entity: Location

Fields: `id` (UUID), `userId` (UUID), `name` (string 255), `address` (string 255, nullable), `notes` (text, nullable), `url` (string 500, nullable), `deletedAt` (datetime, nullable), `createdAt` (datetime).

## Changes

### Domain Layer

- [ ] `src/Domain/Locations/Location.php`: entity with private ctor + `static create()`, soft delete, `update()`
- [ ] `src/Domain/Locations/Locations.php`: repository interface (extends Repository)
- [ ] `src/Domain/Locations/LocationAlreadyDeletedException.php`
- [ ] `src/Domain/Locations/LocationNotFoundException.php` (or use core NotFoundException)

### Infrastructure Layer -- Persistence

- [ ] `src/Infrastructure/Persistence/Doctrine/Mapping/Locations/LocationMapping.php`
- [ ] `src/Infrastructure/Persistence/Doctrine/Mapping/Locations/Locations.php` (DoctrineLocations)
- [ ] `src/Infrastructure/Persistence/InMemory/InMemoryLocations.php`
- [ ] Generate Doctrine migration via `make migrate-gen`

### Infrastructure Layer -- Config

- [ ] `config/common/persistence.php`: register Location DI bindings
- [ ] `config/common/doctrine.php`: register LocationMapping
- [ ] `config/common/openapi/locations.php`: OpenAPI spec for CRUD endpoints

### Application Layer -- Handlers (5 use cases like Mates)

- [ ] `CreateLocation/Command.php`, `Handler.php`, `Result.php`
- [ ] `ListLocations/Query.php`, `Handler.php`, `Result.php`
- [ ] `GetLocation/Query.php`, `Handler.php`, `Result.php`
- [ ] `UpdateLocation/Command.php`, `Handler.php`, `Result.php`
- [ ] `DeleteLocation/Command.php`, `Handler.php`

### Play integration (locationId)

- [ ] `src/Domain/Plays/Play.php`: add `?Uuid $locationId` field, getter, include in `update()`
- [ ] `PlayMapping.php`: add `location_id` (uuid, nullable) field mapping
- [ ] `CreatePlay/Command.php`: add `?Uuid $locationId = null`
- [ ] `CreatePlay/Handler.php`: pass locationId to Play
- [ ] `CreatePlay/Result.php`: add `location_id` to output
- [ ] `UpdatePlay/Command.php`: add `?Uuid $locationId = null`
- [ ] `UpdatePlay/Handler.php`: pass locationId
- [ ] `UpdatePlay/Result.php`: add `location_id` to output
- [ ] `GetPlay/Handler.php`: add `location_id` to output
- [ ] `ListPlays/Handler.php`: add `location_id` to output
- [ ] `config/common/openapi/plays.php`: add `location_id` to schemas
- [ ] Migration for plays_session.location_id column

### Tests

- [ ] Unit test: Location::create(), update(), softDelete()
- [ ] Functional tests: full CRUD cycle (create, list, get, update, delete)
- [ ] Functional test: create/update play with location_id
- [ ] Web/acceptance tests: API endpoints

### Quality Gates

- [ ] `make scan` passes
- [ ] `composer dt:run` passes (Location BC has correct dependencies)
- [ ] All migrations generate cleanly
