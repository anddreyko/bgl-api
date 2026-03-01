# Master Checklist: User Session List (PLAYS-002)

> Feature: PLAYS-002 User Session List
> Created: 2026-03-01

## Overview

**Overall Progress:** 2 of 2 stages completed

**Current Stage:** Done

---

## Stage 1: Application + API Layer (~30min)

**Dependencies:** None

- [x] Create `src/Application/Handlers/Plays/ListPlays/Query.php`
  - Fields: userId (string), page (int=1), size (int=20), gameId (?string), from (?string), to (?string)
- [x] Create `src/Application/Handlers/Plays/ListPlays/Handler.php`
  - Inject: Plays, Games
  - Build Filter from params: Equals(userId) + optional Equals(gameId), Greater(startedAt), Less(startedAt)
  - Use Searchable::search(filter, PageSize, PageNumber, PageSort) + count(filter)
  - For each key from search() -> find() full entity -> transform to array
  - Include game info {id, name} if gameId present
  - Include players array {id, mate_id, score, is_winner, color}
  - Sort: startedAt DESC via PageSort
- [x] Create `src/Application/Handlers/Plays/ListPlays/Result.php`
  - Fields: data (array), total (int), page (int), size (int)
- [x] Register handler in `config/common/bus.php`
- [x] Add serialization mapping in `config/_serialise-mapping.php`
- [x] Add GET to `/v1/plays/sessions` in `config/common/openapi/plays.php`
  - x-message: ListPlays\Query
  - x-interceptors: [AuthInterceptor]
  - x-auth: [userId]
  - Query params: page, size, game_id, from, to
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-2-application-api.md](./stage-2-application-api.md)

---

## Stage 2: Testing + Validation (~30min)

**Dependencies:** Stage 1

- [x] Create `tests/Functional/Plays/ListPlaysCest.php`
  - testListPlaysReturnsUserSessions
  - testListPlaysWithPagination
  - testListPlaysFilterByGameId
  - testListPlaysFilterByDateRange
  - testListPlaysEmptyResult
  - testListPlaysDoesNotShowOtherUserSessions
  - testListPlaysSortedByStartedAtDesc
  - testListPlaysIncludesGameInfo (bonus)
  - testListPlaysIncludesPlayerInfo (bonus)
- [x] Add GET tests to `tests/Web/PlaySessionCest.php`
  - testListSessionsReturns200
  - testListSessionsWithoutTokenReturns401
- [x] Run `make scan` (MANDATORY)
- [x] Run `composer test:func`

Details: [stage-3-testing.md](./stage-3-testing.md)

---

## Quick Reference

### Commands

```bash
composer lp:run      # PHP lint
composer ps:run      # Psalm static analysis
composer test:func   # Functional tests
composer test:web    # Web acceptance tests
make scan            # Full validation (MANDATORY before commit)
```

### Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Plays/ListPlays/Query.php` | CREATE | 1 |
| `src/Application/Handlers/Plays/ListPlays/Handler.php` | CREATE | 1 |
| `src/Application/Handlers/Plays/ListPlays/Result.php` | CREATE | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `config/common/openapi/plays.php` | MODIFY | 1 |
| `tests/Functional/Plays/ListPlaysCest.php` | CREATE | 2 |
| `tests/Web/PlaySessionCest.php` | MODIFY | 2 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-03-01 | Application + API |
| 2 | Done | 2026-03-01 | Testing + Validation |
