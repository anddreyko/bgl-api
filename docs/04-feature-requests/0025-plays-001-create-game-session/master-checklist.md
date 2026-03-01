# Master Checklist: Create Game Session (PLAYS-001)

> Feature: PLAYS-001 Create Game Session
> Task: bgl-1l9
> Created: 2026-02-28

## Overview

**Overall Progress:** 5 of 5 stages completed

**Current Stage:** All stages completed

---

## Stage 1: Domain Layer (~45min)

**Dependencies:** None

- [x] Create `Visibility` enum in `src/Domain/Plays/Entities/Visibility.php`
  - Values: Private, Link, Friends, Registered, Public (string-backed)
- [x] Create `Player` entity in `src/Domain/Plays/Entities/Player.php`
  - Fields: id (Uuid), play (Play), mateId (Uuid), score (?int), isWinner (bool), color (?string)
  - Factory method `Player::create(...)`
  - Immutable (readonly)
- [x] ~~Create `PlayCreated` domain event~~ -- Deferred (no Event Sourcing in MVP, per ADR-006)
- [x] Create `Players` interface extending Repository in `src/Domain/Plays/Entities/Players.php`
- [x] Create `PlayersFactory` interface in `src/Domain/Plays/Entities/PlayersFactory.php`
- [x] Create `EmptyPlayers` null-object in `src/Domain/Plays/Entities/EmptyPlayers.php`
- [x] Extend `Play` entity with new fields:
  - Add `gameId` (?Uuid), `visibility` (Visibility), `players` (Players)
  - New factory method `Play::create(...)` with all fields
  - Methods: `update()`, `finalize()`, `changeVisibility()`, `addPlayer()`
  - `$players` property: non-promoted, untyped (Doctrine/Rector compatibility)
- [x] Unit tests for Player entity
- [x] Unit tests for Play::create() with players
- [x] Unit tests for Visibility enum
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-1-domain.md](./stage-1-domain.md)

---

## Stage 2: Infrastructure Layer (~30min)

**Dependencies:** Stage 1

- [x] Create `PlayerMapping.php` in `src/Infrastructure/Persistence/Doctrine/Mapping/Plays/`
  - Table: `plays_player`
  - Map all Player fields
  - ManyToOne relation to Play
- [x] Create `PlayerCollection.php` -- bridges Domain Players with Doctrine ArrayCollection
- [x] Create `DoctrinePlayersFactory.php` -- factory creating PlayerCollection instances
- [x] Modify `PlayMapping.php`:
  - Add `gameId` field (uuid_vo, nullable)
  - Add `visibility` field (string, enum)
  - Add OneToMany relation to Player (cascade: persist, orphanRemoval: true)
- [x] Generate migration: `make migrate-gen`
- [x] Run migration: `make migrate`
- [x] Validate schema: `make validate-schema`
- [x] Add PlayersFactory DI binding in `config/common/persistence.php`
- [x] Add ParamNameMismatch suppression in psalm.xml for PlayerCollection
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-2-infrastructure.md](./stage-2-infrastructure.md)

---

## Stage 3: Application Layer (~30min)

**Dependencies:** Stage 2

- [x] Rename `OpenSession` to `CreatePlay` in `src/Application/Handlers/Plays/`
- [x] Create `Command.php` in `src/Application/Handlers/Plays/CreatePlay/`
  - Fields: userId (Uuid), name (?string), players (array), gameId (?Uuid),
    startedAt (?DateTime), finishedAt (?DateTime), visibility (string)
- [x] Create `Handler.php` in `src/Application/Handlers/Plays/CreatePlay/`
  - Inject: Plays, Mates, Games, PlayersFactory, UuidGenerator, ClockInterface
  - Validate: no duplicate mateIds, each mate exists and belongs to user, mate not deleted
  - Validate: gameId exists if provided
  - Build Play::create() with Player entities via PlayersFactory
  - If finishedAt provided, immediately finalize
  - Return Result with sessionId
- [x] Create `Result.php` in `src/Application/Handlers/Plays/CreatePlay/`
- [x] Rename `CloseSession` to `FinalizePlay`
- [x] Create `FinalizePlay/Handler.php` -- finds play, validates ownership, finalizes
- [x] Create `UpdatePlay/` -- Command, Handler, Result for updating draft sessions
- [x] Register all 3 handlers in `config/common/bus.php`
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-3-application.md](./stage-3-application.md)

---

## Stage 4: API Layer + Serialization (~20min) [P]

**Dependencies:** Stage 3

- [x] Add POST `/v1/plays/sessions` to `config/common/openapi/plays.php`
  - x-message: CreatePlay\Command
  - x-interceptors: AuthInterceptor
  - x-auth: userId
  - Request body schema with players array, game_id, visibility, etc.
  - OpenAPI validation for players (minItems: 1)
- [x] Add PUT `/v1/plays/sessions/{id}` -- UpdatePlay endpoint
- [x] Add PATCH `/v1/plays/sessions/{id}` -- FinalizePlay endpoint
- [x] Add serialization mappings in `config/_serialise-mapping.php`
  - CreatePlay\Result, FinalizePlay\Result, UpdatePlay\Result
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-4-api.md](./stage-4-api.md)

---

## Stage 5: Testing + Final Validation (~40min)

**Dependencies:** All previous stages

- [x] Functional tests: CreatePlayCest, FinalizePlayCest, UpdatePlayCest
- [x] Web acceptance tests: PlaySessionCest (smoke tests with DB assertions)
- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer dt:run` (architecture check)
- [x] Run `composer test:func` (functional tests)
- [x] Review code for simplification

Details: [stage-5-validation.md](./stage-5-validation.md)

---

## Quick Reference

### Commands

```bash
composer lp:run      # PHP lint
composer ps:run      # Psalm static analysis
composer dt:run      # Deptrac architecture
composer test:unit   # Unit tests
composer test:func   # Functional tests
composer test:web    # Web acceptance tests
composer scan:all    # Full validation (MANDATORY before push)
make migrate-gen     # Generate migration from mapping
make migrate         # Run migrations
make validate-schema # Validate ORM schema
```

### Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Domain/Plays/Entities/Visibility.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/Player.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/Players.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/PlayersFactory.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/EmptyPlayers.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/Play.php` | MODIFY | 1 |
| `src/Infrastructure/.../Mapping/Plays/PlayerMapping.php` | CREATE | 2 |
| `src/Infrastructure/.../Mapping/Plays/PlayerCollection.php` | CREATE | 2 |
| `src/Infrastructure/.../Mapping/Plays/DoctrinePlayersFactory.php` | CREATE | 2 |
| `src/Infrastructure/.../Mapping/Plays/PlayMapping.php` | MODIFY | 2 |
| `config/common/persistence.php` | MODIFY | 2 |
| `src/Application/Handlers/Plays/CreatePlay/Command.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/CreatePlay/Handler.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/CreatePlay/Result.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/FinalizePlay/Command.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/FinalizePlay/Handler.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/FinalizePlay/Result.php` | CREATE (rename) | 3 |
| `src/Application/Handlers/Plays/UpdatePlay/Command.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/UpdatePlay/Handler.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/UpdatePlay/Result.php` | CREATE | 3 |
| `config/common/bus.php` | MODIFY | 3 |
| `config/common/openapi/plays.php` | MODIFY | 4 |
| `config/_serialise-mapping.php` | MODIFY | 4 |
| `tests/Unit/Domain/Plays/Entities/PlayCest.php` | MODIFY | 5 |
| `tests/Functional/Plays/CreatePlayCest.php` | CREATE (rename) | 5 |
| `tests/Functional/Plays/FinalizePlayCest.php` | CREATE (rename) | 5 |
| `tests/Functional/Plays/UpdatePlayCest.php` | CREATE | 5 |
| `tests/Web/PlaySessionCest.php` | MODIFY | 5 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-03-01 | Domain layer with Player, Visibility, Players, PlayersFactory, EmptyPlayers |
| 2 | Done | 2026-03-01 | PlayerMapping, PlayerCollection, DoctrinePlayersFactory, migration |
| 3 | Done | 2026-03-01 | CreatePlay, FinalizePlay, UpdatePlay handlers; OpenSession/CloseSession renamed |
| 4 | Done | 2026-03-01 | 3 endpoints: POST, PUT, PATCH; serialization mappings |
| 5 | Done | 2026-03-01 | Unit, functional, web tests; scan:all passed |

---

## Checkpoints

- [x] After Stage 1: `feat(plays): add Player entity and Visibility enum`
- [x] After Stage 2: `feat(plays): add Player mapping and migration`
- [x] After Stage 3: `feat(plays): add CreatePlay, FinalizePlay, UpdatePlay handlers`
- [x] After Stage 4: `feat(plays): add play session endpoints`
- [x] After Stage 5: `test(plays): add play session tests`
