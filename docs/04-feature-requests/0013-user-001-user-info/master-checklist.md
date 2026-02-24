# Master Checklist: USER-001 User Info

> Task: bgl-u7p
> Created: 2026-02-23

## Overview

**Overall Progress:** 2 of 2 stages completed

---

## Stage 1: Handler + OpenAPI Config (~20min)

**Dependencies:** AUTH-004 complete

- [x] Create `src/Application/Handlers/User/GetUser/Query.php`:
  - Properties: string $userId (from path param)
  - Implements `Message<Result>`
- [x] Create `src/Application/Handlers/User/GetUser/Result.php`:
  - Properties: string $id, string $email, bool $isActive, string $createdAt
- [x] Create `src/Application/Handlers/User/GetUser/Handler.php`:
  - Dependencies: Users
  - Logic: find user -> throw DomainException if not found -> return Result
- [x] Add serialization mapping for Result in `config/_serialise-mapping.php`
- [x] Register handler in `config/common/bus.php`
- [x] Create `config/common/openapi/user.php`:
  - GET /v1/user/{id} with x-interceptors: [AuthInterceptor::class]
  - id from path parameter
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Final Validation (~10min)

**Dependencies:** Stage 1

- [x] Run `composer scan:all` (MANDATORY)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Application/Handlers/User/GetUser/Query.php` | CREATE | 1 |
| `src/Application/Handlers/User/GetUser/Handler.php` | CREATE | 1 |
| `src/Application/Handlers/User/GetUser/Result.php` | CREATE | 1 |
| `config/_serialise-mapping.php` | MODIFY | 1 |
| `config/common/bus.php` | MODIFY | 1 |
| `config/common/openapi/user.php` | CREATE | 1 |
