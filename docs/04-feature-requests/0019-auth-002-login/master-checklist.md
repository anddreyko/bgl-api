# Master Checklist: AUTH-002 Login

> Task: bgl-mrx
> Created: 2026-02-23

## Overview

**Overall Progress:** 3 of 3 stages completed

---

## Stage 1: Refactor LoginByCredentials (~30min)

**Dependencies:** AUTH-001 complete

- [x] Rewrite `src/Application/Handlers/Auth/LoginByCredentials/Command.php`:
  - Change from interface to concrete class
  - Properties: string $email, string $password
  - Implements `Message<Result>`
- [x] Create `src/Application/Handlers/Auth/LoginByCredentials/Result.php`:
  - Properties: string $accessToken, string $refreshToken, int $expiresIn
- [x] Rewrite `src/Application/Handlers/Auth/LoginByCredentials/Handler.php`:
  - Dependencies: Users, PasswordHasher, TokenGenerator, ClockInterface
  - Logic: find by email -> verify password -> check Active status -> generate token pair -> return Result
  - Access token TTL: 7200 (2h), refresh token TTL: 2592000 (30d)
  - JWT payload: ['userId' => $user->getId()->getValue(), 'type' => 'access'|'refresh']
- [x] Move/remove old exceptions: clean up UserBannedException, UserNotRegisterException or reuse
- [x] Add serialization mapping for Result in `config/_serialise-mapping.php`
- [x] Register handler in `config/common/bus.php` (replace old registration if any)
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: OpenAPI Config + Tests (~25min)

**Dependencies:** Stage 1

- [x] Add to `config/common/openapi/auth.php`:
  - POST /v1/auth/password/sign-in -> LoginByCredentials\Command (email, password in body)
- [x] Create unit tests for Handler
- [x] Verify: `composer test:unit`

---

## Stage 3: Final Validation (~10min)

**Dependencies:** Stage 2

- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer dt:run`

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Auth/LoginByCredentials/Command.php` | REWRITE | 1 |
| `src/Application/Handlers/Auth/LoginByCredentials/Handler.php` | REWRITE | 1 |
| `src/Application/Handlers/Auth/LoginByCredentials/Result.php` | CREATE | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/auth.php` | MODIFY | 2 |
