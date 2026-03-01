# Documentation: Create Game Session (PLAYS-001)

> FR: 0025-plays-001-create-game-session
> Completed: 2026-03-01

## Summary

Extended the Plays bounded context with full game session management: creating play sessions with
optional players, game link, and visibility; updating draft sessions; and finalizing (publishing)
them. The implementation adds a Player entity, Visibility enum, and three CQS handlers
(CreatePlay, UpdatePlay, FinalizePlay) exposed via REST API endpoints.

This replaces the earlier simple OpenSession/CloseSession handlers (renamed to CreatePlay/FinalizePlay)
and introduces richer domain modeling with player tracking and access-control visibility levels.

## API Endpoints

### POST /v1/plays/sessions -- Create Play Session

Creates a new play session in Draft status. All fields are optional except the auth token.

Request body (all optional):
- `name` (string) -- session display name
- `game_id` (string, uuid) -- link to a game from the catalog
- `started_at` (string, date-time) -- defaults to server time
- `finished_at` (string, date-time) -- if provided, session is immediately finalized
- `visibility` (enum: private, link, friends, registered, public) -- defaults to "private"
- `players` (array of objects) -- each with `mate_id` (required), `score`, `is_winner`, `color`

Response: `{ code: 0, data: { session_id: "<uuid>" } }`

### PUT /v1/plays/sessions/{id} -- Update Play Session

Full replace of mutable fields on a Draft session. Omitted fields reset to defaults.

Request body (all optional):
- `name` (string, nullable)
- `game_id` (string, uuid, nullable)
- `visibility` (enum, defaults to "private")

Response: `{ code: 0, data: { session_id: "<uuid>" } }`

### PATCH /v1/plays/sessions/{id} -- Finalize Play Session

Transitions a Draft session to Published status. Sets finishedAt timestamp.

Request body (optional):
- `finished_at` (string, date-time) -- defaults to server time

Response: `{ code: 0, data: { session_id, started_at, finished_at } }`

All endpoints require Bearer token authentication via AuthInterceptor.

## Domain Model

### Visibility (enum)

String-backed enum with five levels controlling who can view a play session:
Private, Link, Friends, Registered, Public.

Location: `src/Domain/Plays/Entities/Visibility.php`

### Player (entity)

Immutable (readonly) entity representing a participant in a play session. Links to a Mate
from the user's co-player directory.

Fields: id (Uuid), play (Play), mateId (Uuid), score (?int), isWinner (bool), color (?string).

Invariants:
- Score cannot be negative
- Color cannot exceed 50 characters

Location: `src/Domain/Plays/Entities/Player.php`

### Players (repository interface) and EmptyPlayers (null-object)

`Players` extends the generic `Repository<Player>` contract.
`EmptyPlayers` is a null-object used as the default initializer in the Play constructor,
later replaced by either `PlayerCollection` (Doctrine) or the factory output.

Locations:
- `src/Domain/Plays/Entities/Players.php`
- `src/Domain/Plays/Entities/EmptyPlayers.php`

### PlayersFactory (interface)

Factory contract that decouples the Domain from the Infrastructure layer.
The Application layer injects this to create empty Players collections without
depending on Doctrine's `PlayerCollection` directly. Required by Deptrac rules.

Location: `src/Domain/Plays/Entities/PlayersFactory.php`

### Play (entity, modified)

Extended with:
- `$gameId` (?Uuid) -- optional game catalog reference
- `$visibility` (Visibility) -- access control level, defaults to Private
- `$players` (Players) -- non-promoted, untyped property (see Architecture Decisions)
- `create()` factory method -- creates a Draft play with all fields
- `update()` -- replaces name, gameId, visibility (Draft only)
- `finalize()` -- transitions Draft to Published, sets finishedAt
- `changeVisibility()` -- standalone visibility change (Draft only)
- `addPlayer()` -- adds a Player to the collection

Location: `src/Domain/Plays/Entities/Play.php`

## Architecture Decisions

### Players property: non-promoted, untyped

The `$players` property on Play is declared as `private $players` without a type hint and outside
the constructor parameter list. This avoids a conflict between:
- Rector's `ReadOnlyPropertyRector` (wants to add `readonly` to typed properties)
- Doctrine's `PersistentCollection` (replaces the collection at hydration time, incompatible with readonly)

By keeping `$players` untyped and non-promoted, Rector skips it and Doctrine can swap
the collection freely.

### PlayersFactory pattern

The `CreatePlay\Handler` needs to create a `Players` collection, but the concrete
implementation (`PlayerCollection`) lives in Infrastructure. Direct dependency would violate
Deptrac's layer rules (Application must not depend on Infrastructure).

Solution: `PlayersFactory` interface in Domain, `DoctrinePlayersFactory` in Infrastructure,
wired via DI container.

### EmptyPlayers null-object

Psalm reports `PropertyNotSetInConstructor` for the `$players` field because it is assigned
inside the constructor body (not via promotion). `EmptyPlayers` serves as the default value,
satisfying both Psalm and the domain invariant that a Play always has a Players reference.

### ParamNameMismatch suppression

`PlayerCollection` extends Doctrine's `ArrayCollection` and implements `Players` (which extends
`Repository`). The two interfaces define methods with different parameter names for the same
signatures (`$entity` vs `$element`/$`$key`). This is a structural incompatibility that cannot
be resolved in code, so `ParamNameMismatch` is suppressed for `PlayerCollection` in psalm.xml.

## Files Created/Modified

### Created

| File | Layer | Purpose |
|------|-------|---------|
| `src/Domain/Plays/Entities/Visibility.php` | Domain | Visibility enum |
| `src/Domain/Plays/Entities/Player.php` | Domain | Player entity |
| `src/Domain/Plays/Entities/Players.php` | Domain | Players repository interface |
| `src/Domain/Plays/Entities/PlayersFactory.php` | Domain | Factory interface |
| `src/Domain/Plays/Entities/EmptyPlayers.php` | Domain | Null-object for Players |
| `src/Infrastructure/.../Mapping/Plays/PlayerMapping.php` | Infrastructure | Doctrine mapping for Player |
| `src/Infrastructure/.../Mapping/Plays/PlayerCollection.php` | Infrastructure | ArrayCollection bridge |
| `src/Infrastructure/.../Mapping/Plays/DoctrinePlayersFactory.php` | Infrastructure | Factory implementation |
| `src/Application/Handlers/Plays/CreatePlay/Command.php` | Application | Create play command |
| `src/Application/Handlers/Plays/CreatePlay/Handler.php` | Application | Create play handler |
| `src/Application/Handlers/Plays/CreatePlay/Result.php` | Application | Create play result |
| `src/Application/Handlers/Plays/FinalizePlay/Command.php` | Application | Finalize play command |
| `src/Application/Handlers/Plays/FinalizePlay/Handler.php` | Application | Finalize play handler |
| `src/Application/Handlers/Plays/FinalizePlay/Result.php` | Application | Finalize play result |
| `src/Application/Handlers/Plays/UpdatePlay/Command.php` | Application | Update play command |
| `src/Application/Handlers/Plays/UpdatePlay/Handler.php` | Application | Update play handler |
| `src/Application/Handlers/Plays/UpdatePlay/Result.php` | Application | Update play result |

### Modified

| File | Change |
|------|--------|
| `src/Domain/Plays/Entities/Play.php` | Added gameId, visibility, players, new methods |
| `src/Infrastructure/.../Mapping/Plays/PlayMapping.php` | Added gameId, visibility, OneToMany players |
| `config/common/bus.php` | Registered CreatePlay, FinalizePlay, UpdatePlay handlers |
| `config/common/openapi/plays.php` | All 3 endpoint definitions |
| `config/_serialise-mapping.php` | Result mappings for all 3 handlers |
| `config/common/persistence.php` | PlayersFactory DI binding |
| `psalm.xml` | ParamNameMismatch suppression for PlayerCollection |

### Renamed (from previous simple handlers)

| Old Path | New Path |
|----------|----------|
| `src/Application/Handlers/Plays/OpenSession/*` | `src/Application/Handlers/Plays/CreatePlay/*` |
| `src/Application/Handlers/Plays/CloseSession/*` | `src/Application/Handlers/Plays/FinalizePlay/*` |

## Test Coverage

### Unit Tests

`tests/Unit/Domain/Plays/Entities/PlayCest.php`:
- Play::create() with all fields
- Play::update() on draft
- Play::update() on published (throws DomainException)
- Play::finalize() transitions to Published
- Play::finalize() on non-draft (throws DomainException)
- Play::finalize() with invalid time (throws DomainException)
- Play::changeVisibility() on draft
- Player::create() with negative score (throws DomainException)
- Player::create() with oversized color (throws DomainException)

### Functional Tests

- `tests/Functional/Plays/CreatePlayCest.php` (was OpenSessionCest)
- `tests/Functional/Plays/FinalizePlayCest.php` (was CloseSessionCest)
- `tests/Functional/Plays/UpdatePlayCest.php`

### Web Acceptance Tests

`tests/Web/PlaySessionCest.php`:
- Smoke tests with DB assertions for player persistence
- POST, PUT, PATCH endpoints with auth
