# Master Checklist: Create Game Session (PLAYS-001)

> Feature: PLAYS-001 Create Game Session
> Task: bgl-1l9
> Created: 2026-02-28

## Overview

**Overall Progress:** 0 of 5 stages completed

**Current Stage:** Stage 1 - Domain Layer

---

## Stage 1: Domain Layer (~45min)

**Dependencies:** None

- [ ] Create `Visibility` enum in `src/Domain/Plays/Entities/Visibility.php`
  - Values: Private, Link, Friends, Registered, Public (string-backed)
- [ ] Create `Player` entity in `src/Domain/Plays/Entities/Player.php`
  - Fields: id (Uuid), playId (Uuid), mateId (Uuid), score (?int), isWinner (bool), color (?string)
  - Factory method `Player::create(...)`
  - Immutable (readonly)
- [ ] Create `PlayCreated` domain event in `src/Domain/Plays/Events/PlayCreated.php`
  - Fields: playId (string), userId (string)
- [ ] Extend `Play` entity with new fields:
  - Add `gameId` (?Uuid), `visibility` (Visibility), `players` (array of Player)
  - New factory method `Play::create(...)` with all fields (keep `Play::open()` for backward compat)
  - Method `getPlayers()`, `getGameId()`, `getVisibility()`
  - Emit PlayCreated event via Emits trait
- [ ] Unit tests for Player entity
- [ ] Unit tests for Play::create() with players
- [ ] Unit tests for Visibility enum
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-1-domain.md](./stage-1-domain.md)

---

## Stage 2: Infrastructure Layer (~30min)

**Dependencies:** Stage 1

- [ ] Create `PlayerMapping.php` in `src/Infrastructure/Persistence/Doctrine/Mapping/Plays/`
  - Table: `plays_player`
  - Map all Player fields
  - ManyToOne relation to Play
- [ ] Modify `PlayMapping.php`:
  - Add `gameId` field (uuid_vo, nullable)
  - Add `visibility` field (string, enum)
  - Add OneToMany relation to Player (cascade: persist, orphanRemoval: true)
- [ ] Generate migration: `make migrate-gen`
- [ ] Run migration: `make migrate`
- [ ] Validate schema: `make validate-schema`
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-2-infrastructure.md](./stage-2-infrastructure.md)

---

## Stage 3: Application Layer (~30min)

**Dependencies:** Stage 2

- [ ] Create `Command.php` in `src/Application/Handlers/Plays/CreateSession/`
  - Fields: userId (string), gameId (?string), name (?string), startedAt (?string),
    finishedAt (?string), visibility (?string), players (array)
  - players: array of arrays with keys: mateId, score, isWinner, color
- [ ] Create `Handler.php` in `src/Application/Handlers/Plays/CreateSession/`
  - Inject: Plays, Mates, Games (optional), UuidGenerator, ClockInterface
  - Validate: players not empty, no duplicate mateIds
  - Validate: each mateId exists and belongs to userId
  - Validate: gameId exists if provided
  - Build Play::create() with Player entities
  - Return Result with sessionId
- [ ] Create `Result.php` in `src/Application/Handlers/Plays/CreateSession/`
  - Field: sessionId (string)
- [ ] Register in `config/common/bus.php`
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-3-application.md](./stage-3-application.md)

---

## Stage 4: API Layer + Serialization (~20min) [P]

**Dependencies:** Stage 3

- [ ] Add POST `/v1/plays` to `config/common/openapi/plays.php`
  - x-message: CreateSession\Command
  - x-interceptors: AuthInterceptor
  - x-auth: userId
  - Request body schema with players array, game_id, visibility, etc.
  - OpenAPI validation for players (minItems: 1)
- [ ] Add serialization mapping in `config/_serialise-mapping.php`
  - CreateSession\Result -> {id: sessionId}
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-4-api.md](./stage-4-api.md)

---

## Stage 5: Testing + Final Validation (~40min)

**Dependencies:** All previous stages

- [ ] Functional tests in `tests/Functional/Plays/CreateSessionCest.php`
  - Success: create with all fields
  - Success: create with minimal fields (defaults)
  - Error: empty players array
  - Error: non-existent mateId
  - Error: mate belongs to another user
  - Error: duplicate mateId in players
- [ ] Web acceptance tests in `tests/Web/PlaysCest.php`
  - POST /v1/plays with valid data returns 200
  - POST /v1/plays without token returns 401
  - POST /v1/plays with empty players returns 422
- [ ] Run `composer scan:all` (MANDATORY)
- [ ] Run `composer dt:run` (architecture check)
- [ ] Run `composer test:func` (functional tests)
- [ ] Review code for simplification

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
| `src/Domain/Plays/Events/PlayCreated.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/Play.php` | MODIFY | 1 |
| `src/Infrastructure/.../Mapping/Plays/PlayerMapping.php` | CREATE | 2 |
| `src/Infrastructure/.../Mapping/Plays/PlayMapping.php` | MODIFY | 2 |
| `src/Infrastructure/Database/Migrations/VersionXXX.php` | AUTO-GEN | 2 |
| `src/Application/Handlers/Plays/CreateSession/Command.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/CreateSession/Handler.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/CreateSession/Result.php` | CREATE | 3 |
| `config/common/bus.php` | MODIFY | 3 |
| `config/common/openapi/plays.php` | MODIFY | 4 |
| `config/_serialise-mapping.php` | MODIFY | 4 |
| `tests/Functional/Plays/CreateSessionCest.php` | CREATE | 5 |
| `tests/Web/PlaysCest.php` | MODIFY | 5 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Not Started | - | |
| 2 | Not Started | - | |
| 3 | Not Started | - | |
| 4 | Not Started | - | |
| 5 | Not Started | - | |

---

## Checkpoints

- [ ] After Stage 1: `feat(plays): add Player entity and Visibility enum`
- [ ] After Stage 2: `feat(plays): add Player mapping and migration`
- [ ] After Stage 3: `feat(plays): add CreateSession handler`
- [ ] After Stage 4: `feat(plays): add POST /v1/plays endpoint`
- [ ] After Stage 5: `test(plays): add CreateSession tests`
