# Master Checklist: AUTH-003 Token Refresh

> Task: bgl-8f2
> Created: 2026-02-23

## Overview

**Overall Progress:** 3 of 3 stages completed

---

## Stage 1: Handler Implementation (~20min)

**Dependencies:** AUTH-002 complete

- [x] Create `src/Application/Handlers/Auth/RefreshToken/Command.php`:
  - Properties: string $refreshToken
  - Implements `Message<Result>`
- [x] Create `src/Application/Handlers/Auth/RefreshToken/Result.php`:
  - Properties: string $accessToken, string $refreshToken, int $expiresIn
  - (Same structure as LoginByCredentials\Result)
- [x] Create `src/Application/Handlers/Auth/RefreshToken/Handler.php`:
  - Dependencies: TokenGenerator, Users
  - Logic: verify refresh token -> extract userId -> check type=refresh -> find user -> check Active -> generate new pair
- [x] Add serialization mapping for Result in `config/_serialise-mapping.php`
- [x] Register handler in `config/common/bus.php`
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: OpenAPI Config + Tests (~15min)

**Dependencies:** Stage 1

- [x] Add to `config/common/openapi/auth.php`:
  - POST /v1/auth/refresh -> RefreshToken\Command (refreshToken in body)
- [x] Create unit tests for Handler
- [x] Verify: `composer test:unit`

---

## Stage 3: Final Validation (~10min)

**Dependencies:** Stage 2

- [x] Run `composer scan:all` (MANDATORY)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Auth/RefreshToken/Command.php` | CREATE | 1 |
| `src/Application/Handlers/Auth/RefreshToken/Handler.php` | CREATE | 1 |
| `src/Application/Handlers/Auth/RefreshToken/Result.php` | CREATE | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/auth.php` | MODIFY | 2 |
