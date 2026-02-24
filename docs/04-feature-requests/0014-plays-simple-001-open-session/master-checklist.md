# Master Checklist: PLAYS-001 Open Game Session

> Task: bgl-edt
> Created: 2026-02-23

## Overview

**Overall Progress:** 4 of 4 stages completed

---

## Stage 1: Domain Layer (~20min)

**Dependencies:** AUTH-004 complete

- [x] Create `src/Domain/Plays/Entities/Session.php`:
  - Properties: Uuid $id, string $name, string $userId, SessionStatus $status, \DateTimeImmutable $startedAt, ?\DateTimeImmutable $finishedAt
  - Static factory: `open(Uuid $id, string $userId, ?string $name, \DateTimeImmutable $startedAt): self`
  - Method: `close(\DateTimeImmutable $finishedAt): void`
- [x] Create `src/Domain/Plays/Entities/SessionStatus.php` enum:
  - Cases: Open, Closed
- [x] Create `src/Domain/Plays/Entities/Sessions.php` repository interface:
  - Extends Repository<Session>
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Infrastructure Layer (~25min)

**Dependencies:** Stage 1

- [x] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Plays/SessionMapping.php`
- [x] Create `src/Infrastructure/Persistence/Doctrine/Plays/Sessions.php` (Doctrine impl)
- [x] Register mapping in `config/common/doctrine.php`
- [x] Register repository in `config/common/persistence.php`
- [x] Generate migration: `make migrate-gen` (creates records_session table)
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 3: Application Layer + OpenAPI (~20min)

**Dependencies:** Stage 2

- [x] Create `src/Application/Handlers/Plays/OpenSession/Command.php`:
  - Properties: string $userId, ?string $name, ?string $startedAt
  - Implements `Message<Result>`
- [x] Create `src/Application/Handlers/Plays/OpenSession/Result.php`:
  - Properties: string $sessionId
- [x] Create `src/Application/Handlers/Plays/OpenSession/Handler.php`:
  - Dependencies: Sessions, ClockInterface
  - Logic: create Session::open() -> add to repo -> return Result
- [x] Add serialization mapping for Result in `config/_serialise-mapping.php`
- [x] Register handler in `config/common/bus.php`
- [x] Create `config/common/openapi/plays.php`:
  - POST /v1/plays/sessions with x-interceptors: [AuthInterceptor::class]
  - userId from x-source: attribute:auth.userId, name and startedAt from body
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 4: Final Validation (~10min)

**Dependencies:** Stage 3

- [x] Run `composer scan:all` (MANDATORY)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Domain/Plays/Entities/Session.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/SessionStatus.php` | CREATE | 1 |
| `src/Domain/Plays/Entities/Sessions.php` | CREATE | 1 |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Plays/SessionMapping.php` | CREATE | 2 |
| `src/Infrastructure/Persistence/Doctrine/Plays/Sessions.php` | CREATE | 2 |
| `config/common/doctrine.php` | MODIFY | 2 |
| `config/common/persistence.php` | MODIFY | 2 |
| `src/Application/Handlers/Plays/OpenSession/Command.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/OpenSession/Handler.php` | CREATE | 3 |
| `src/Application/Handlers/Plays/OpenSession/Result.php` | CREATE | 3 |
| `config/_serialise-mapping.php` | MODIFY | 3 |
| `config/common/bus.php` | MODIFY | 3 |
| `config/common/openapi/plays.php` | CREATE | 3 |
