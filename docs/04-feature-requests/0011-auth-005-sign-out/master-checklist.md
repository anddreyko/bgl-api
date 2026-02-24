# Master Checklist: AUTH-005 Sign Out

> Task: bgl-tda
> Created: 2026-02-23

## Overview

**Overall Progress:** 2 of 2 stages completed

---

## Stage 1: Handler + OpenAPI Config (~15min)

**Dependencies:** AUTH-004 complete

- [x] Create `src/Application/Handlers/Auth/SignOut/Command.php`:
  - Properties: string $userId (from AuthInterceptor via x-source)
  - Implements `Message<string>`
- [x] Create `src/Application/Handlers/Auth/SignOut/Handler.php`:
  - MVP: return 'sign out' (client-side token deletion)
- [x] Register handler in `config/common/bus.php`
- [x] Add to `config/common/openapi/auth.php`:
  - POST /v1/auth/sign-out with x-interceptors: [AuthInterceptor::class]
  - userId property with x-source: attribute:auth.userId
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Final Validation (~10min)

**Dependencies:** Stage 1

- [x] Run `composer scan:all` (MANDATORY)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/Auth/SignOut/Command.php` | CREATE | 1 |
| `src/Application/Handlers/Auth/SignOut/Handler.php` | CREATE | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/auth.php` | MODIFY | 1 |
