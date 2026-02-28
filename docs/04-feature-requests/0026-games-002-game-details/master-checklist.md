# Master Checklist: Game Details (GAMES-002)

> Feature: GAMES-002 Game Details
> Task: bgl-y15
> Created: 2026-02-28

## Overview

**Overall Progress:** 0 of 3 stages completed

**Current Stage:** Stage 1 - Application + API

---

## Stage 1: Application + API (~25min)

**Dependencies:** None

- [ ] Create `Query.php` in `src/Application/Handlers/Games/GetGame/`
  - Fields: gameId (string)
- [ ] Create `Handler.php` in `src/Application/Handlers/Games/GetGame/`
  - Inject: Games repository
  - Find game by ID, throw DomainException if not found
  - Return Result with all Game fields
- [ ] Create `Result.php` in `src/Application/Handlers/Games/GetGame/`
  - Fields: id (string), bggId (int), name (string), yearPublished (?int)
- [ ] Register in `config/common/bus.php`
- [ ] Add GET `/v1/games/{id}` to `config/common/openapi/games.php`
  - x-message: GetGame\Query
  - x-map: id -> gameId
  - Parameters: id (path, required, string)
  - Responses: 200 (success), 404 (not found), 500 (error)
- [ ] Add serialization mapping in `config/_serialise-mapping.php`
  - GetGame\Result -> {id, bgg_id, name, year_published}
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-1-application-api.md](./stage-1-application-api.md)

---

## Stage 2: Testing (~20min)

**Dependencies:** Stage 1

- [ ] Functional tests in `tests/Functional/Games/GetGameCest.php`
  - Success: find existing game returns Result
  - Error: non-existent game throws DomainException
- [ ] Web acceptance tests in `tests/Web/GamesCest.php` (extend existing)
  - GET /v1/games/{id} with valid ID returns 200
  - GET /v1/games/{id} with non-existent ID returns 404
- [ ] Verify: `composer lp:run && composer ps:run`

Details: [stage-2-testing.md](./stage-2-testing.md)

---

## Stage 3: Final Validation (~10min)

**Dependencies:** All previous stages

- [ ] Run `composer scan:all` (MANDATORY)
- [ ] Run `composer dt:run` (architecture check)
- [ ] Run `composer test:func` (functional tests)
- [ ] Run `composer test:web` (acceptance tests)
- [ ] Review code for simplification

Details: [stage-3-validation.md](./stage-3-validation.md)

---

## Quick Reference

### Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Games/GetGame/Query.php` | CREATE | 1 |
| `src/Application/Handlers/Games/GetGame/Handler.php` | CREATE | 1 |
| `src/Application/Handlers/Games/GetGame/Result.php` | CREATE | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/games.php` | MODIFY | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `tests/Functional/Games/GetGameCest.php` | CREATE | 2 |
| `tests/Web/GamesCest.php` | MODIFY | 2 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Not Started | - | |
| 2 | Not Started | - | |
| 3 | Not Started | - | |

---

## Checkpoints

- [ ] After Stage 1: `feat(games): add GetGame endpoint`
- [ ] After Stage 2: `test(games): add GetGame tests`
- [ ] After Stage 3: verify `composer scan:all` passes
