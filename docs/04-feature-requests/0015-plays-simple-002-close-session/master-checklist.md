# Master Checklist: PLAYS-002 Close Game Session

> Task: bgl-q82
> Created: 2026-02-23

## Overview

**Overall Progress:** 2 of 2 stages completed

---

## Stage 1: Handler + OpenAPI Config (~20min)

**Dependencies:** PLAYS-001 complete

- [x] Create `src/Application/Handlers/Plays/ClosePlay/Command.php`:
  - Properties: string $playId, string $userId, ?string $startedAt, ?string $finishedAt
  - Implements `Message<Result>`
- [x] Create `src/Application/Handlers/Plays/ClosePlay/Result.php`:
  - Properties: string $playId, string $startedAt, string $finishedAt
- [x] Create `src/Application/Handlers/Plays/ClosePlay/Handler.php`:
  - Dependencies: Plays, ClockInterface
  - Logic: find play -> validate ownership (userId match) -> close -> return Result
- [x] Add serialization mapping for Result in `config/_serialise-mapping.php`
- [x] Register handler in `config/common/bus.php`
- [x] Add to `config/common/openapi/plays.php`:
  - PATCH /v1/plays/sessions/{id} with x-interceptors: [AuthInterceptor::class]
  - id from path, userId from x-source: attribute:auth.userId, startedAt/finishedAt from body
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Final Validation (~10min)

**Dependencies:** Stage 1

- [x] Run `composer scan:all` (MANDATORY)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Plays/ClosePlay/Command.php` | CREATE | 1 |
| `src/Application/Handlers/Plays/ClosePlay/Handler.php` | CREATE | 1 |
| `src/Application/Handlers/Plays/ClosePlay/Result.php` | CREATE | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/plays.php` | MODIFY | 1 |
