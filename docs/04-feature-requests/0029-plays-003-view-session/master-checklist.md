# Master Checklist: View Session (PLAYS-003)

> Feature: PLAYS-003 View Session
> Created: 2026-03-01

## Overview

**Overall Progress:** 3 of 3 stages completed

**Current Stage:** Done

---

## Stage 1: OptionalAuthInterceptor (~20min)

**Dependencies:** None

- [x] Create `src/Presentation/Api/Interceptors/OptionalAuthInterceptor.php`
  - If Bearer token present: verify and set auth.userId attribute
  - If no token or invalid: set auth.userId = null (no exception)
  - Follow AuthInterceptor structure
- [x] Unit test: `tests/Unit/Presentation/Api/Interceptors/OptionalAuthInterceptorCest.php`
  - testWithValidTokenSetsUserId
  - testWithoutTokenSetsNull
  - testWithInvalidTokenSetsNull
  - testWithNonBearerHeaderSetsNull (bonus)
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-1-optional-auth.md](./stage-1-optional-auth.md)

---

## Stage 2: Application + API Layer (~30min)

**Dependencies:** Stage 1

- [x] Create `src/Application/Handlers/Plays/GetPlay/Query.php`
  - Fields: playId (string), userId (?string = null)
- [x] Create `src/Application/Handlers/Plays/GetPlay/Handler.php`
  - Inject: Plays, Games, Mates
  - Find Play by ID, throw NotFoundException if not found
  - Visibility access control:
    - Draft: owner only
    - Private: owner only
    - Link: anyone
    - Public: anyone
    - Authenticated: userId != null, else throw AuthenticationException
    - Participants: owner OR requesting user's mateId is among play players; anonymous -> AuthenticationException
  - Return 404 (NotFoundException) for access denied (not 403)
  - Transform Play + Players + Game to Result
- [x] Create `src/Application/Handlers/Plays/GetPlay/Result.php`
  - Fields: id, name, status, visibility, startedAt, finishedAt, game (?array{id,name}), players (array)
- [x] Register handler in `config/common/bus.php`
- [x] Add serialization mapping in `config/_serialise-mapping.php`
- [x] Add GET `/v1/plays/sessions/{id}` to `config/common/openapi/plays.php`
  - x-message: GetPlay\Query
  - x-interceptors: [OptionalAuthInterceptor]
  - x-auth: [userId]
  - x-map: [id => playId]
  - Path param: id (required, uuid)
- [x] Verify: `composer lp:run && composer ps:run`

Details: [stage-2-application-api.md](./stage-2-application-api.md)

---

## Stage 3: Testing + Validation (~40min)

**Dependencies:** Stage 2

- [x] Create `tests/Functional/Plays/GetPlayCest.php`
  - testOwnerViewsPrivateSession
  - testNonOwnerDeniedPrivateSession (NotFoundException)
  - testAnonymousViewsPublicSession
  - testAnonymousViewsLinkSession
  - testAnonymousDeniedAuthenticatedSession (AuthenticationException)
  - testAuthenticatedViewsAuthenticatedSession
  - testParticipantsVisibilityPlayerAccess
  - testAnonymousDeniedParticipantsSession (AuthenticationException)
  - testParticipantsVisibilityNonPlayerDenied
  - testNonExistentSessionThrowsNotFound
  - testDraftSessionOwnerOnly
  - testAnonymousDeniedDraftSession (bonus)
  - testResultContainsPlayerData (bonus)
- [x] Add GET tests to `tests/Web/PlaySessionCest.php`
  - testGetSessionReturns200ForOwner
  - testGetSessionReturns404ForNonExistent
- [x] Run `make scan` (MANDATORY)
- [x] Run `composer test:func`

Details: [stage-3-testing.md](./stage-3-testing.md)

---

## Quick Reference

### Commands

```bash
composer lp:run      # PHP lint
composer ps:run      # Psalm static analysis
composer test:unit   # Unit tests
composer test:func   # Functional tests
composer test:web    # Web acceptance tests
make scan            # Full validation (MANDATORY before commit)
```

### Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Presentation/Api/Interceptors/OptionalAuthInterceptor.php` | CREATE | 1 |
| `tests/Unit/Presentation/Api/Interceptors/OptionalAuthInterceptorCest.php` | CREATE | 1 |
| `src/Application/Handlers/Plays/GetPlay/Query.php` | CREATE | 2 |
| `src/Application/Handlers/Plays/GetPlay/Handler.php` | CREATE | 2 |
| `src/Application/Handlers/Plays/GetPlay/Result.php` | CREATE | 2 |
| `config/common/bus.php` | MODIFY | 2 |
| `config/_serialise-mapping.php` | MODIFY | 2 |
| `config/common/openapi/plays.php` | MODIFY | 2 |
| `tests/Functional/Plays/GetPlayCest.php` | CREATE | 3 |
| `tests/Web/PlaySessionCest.php` | MODIFY | 3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-03-01 | OptionalAuthInterceptor |
| 2 | Done | 2026-03-01 | Application + API |
| 3 | Done | 2026-03-01 | Testing + Validation |
