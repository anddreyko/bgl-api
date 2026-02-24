# Master Checklist: OpenAPI Request Validation Middleware

> Task: bgl-sjn
> Created: 2026-02-23

## Overview

**Overall Progress:** 3 of 3 stages completed
**Current Stage:** Complete

---

## Stage 1: Install Dependency + Middleware (~25min)

**Dependencies:** None

- [x] Install `league/openapi-psr7-validator` via `docker compose run --rm api-php-cli composer require league/openapi-psr7-validator`
- [x] Create `src/Presentation/Api/Middleware/OpenApiValidationMiddleware.php` implementing PSR-15 MiddlewareInterface
  - Constructor: receives OpenAPI spec (cebe\openapi\spec\OpenApi) or ServerRequestValidator
  - process(): validates request, on failure returns ErrorResponse::validation() as JSON 422
  - Routes NOT in the spec pass through without validation
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: DI Config + Registration (~15min)

**Dependencies:** Stage 1

- [x] Create `config/common/openapi-validator.php` with DI config for the middleware
  - Build ServerRequestValidator from cebe OpenApi spec object
- [x] Register middleware in `web/index.php` BEFORE the catch-all route (after routing middleware, before ApiAction)
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 3: Tests + Final Validation (~20min)

**Dependencies:** Stage 2

- [x] Create `tests/Functional/Api/OpenApiValidationMiddlewareCest.php`
  - Test: valid request passes through
  - Test: missing required field returns 422
  - Test: wrong type returns 422
  - Test: route not in spec passes through
- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer dt:run` (architecture check)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Presentation/Api/Middleware/OpenApiValidationMiddleware.php` | CREATE | 1 |
| `config/common/openapi-validator.php` | CREATE | 2 |
| `web/index.php` | MODIFY | 2 |
| `tests/Functional/Api/OpenApiValidationMiddlewareCest.php` | CREATE | 3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Completed | 2026-02-23 | Middleware created |
| 2 | Completed | 2026-02-23 | DI config and registration |
| 3 | Completed | 2026-02-23 | Tests and validation |

---

## Architecture Notes

Current OpenAPI spec is built from PHP arrays via Laminas ConfigAggregator, merged into `cebe\openapi\spec\OpenApi`.
The middleware needs to build a `league/openapi-psr7-validator` ServerRequestValidator from this OpenApi object.

The middleware sits in Presentation layer (PSR-15 middleware), which is correct per dependency law.
