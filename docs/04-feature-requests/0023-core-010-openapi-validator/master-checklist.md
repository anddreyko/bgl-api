# Master Checklist: CORE-010 League OpenAPI Validator

> Task: bgl-dpk
> Created: 2026-02-23
> Depends on: bgl-aqk (CORE-009)

## Overview

**Overall Progress:** 0 of 3 stages completed
**Current Stage:** Stage 1

---

## Stage 1: Install Package + OpenApi Spec Factory (~30min)

**Dependencies:** CORE-009 completed

- [ ] Install `league/openapi-psr7-validator` via `docker compose run --rm api-php-cli composer require league/openapi-psr7-validator`
- [ ] Create DI factory that builds `cebe\openapi\spec\OpenApi` from PHP-array configs:
  - Load all `config/common/openapi/*.php` files
  - Merge paths into single array
  - Add `openapi: 3.0.0`, `info`, `servers` metadata
  - Construct `new \cebe\openapi\spec\OpenApi($mergedArray)`
- [ ] Register `ValidationMiddleware` via `ValidationMiddlewareBuilder::fromSchema()`
- [ ] Add `SlimAdapter` wrapping the PSR-15 middleware to Slim middleware stack
- [ ] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Wire AttributeInputValidator + Update ApiAction (~30min)

**Dependencies:** Stage 1

- [ ] Update `src/Presentation/Api/ApiAction.php`:
  - Remove `RequestValidator` dependency (replaced by League middleware)
  - Remove explicit `$this->requestValidator->validate()` call
  - Add `InputValidator` dependency (AttributeInputValidator)
  - After hydrating Command: call `$this->inputValidator->validate($command)`
  - If `ValidationResult::hasErrors()`, return 422 with structured errors
- [ ] Update DI config: remove RequestValidator binding, add InputValidator if not already bound
- [ ] Write functional tests in `tests/Functional/` (Application layer -> Functional suite per ADR-015):
  - Valid request passes both middleware and attribute validation
  - Invalid body rejected by League middleware (400)
  - Valid body but invalid attribute (e.g. short password) rejected by AttributeInputValidator (422)
- [ ] Verify: `composer lp:run && composer ps:run && composer test:func`
- [ ] Run `composer test:web` -- E2E must pass

---

## Stage 3: Cleanup + Final Validation (~15min)

**Dependencies:** Stage 2

- [ ] Delete `src/Infrastructure/Http/OpenApiRequestValidator.php`
- [ ] Delete `src/Core/Http/RequestValidator.php` (if no other usages)
- [ ] Remove old DI bindings for deleted classes
- [ ] Delete or update related unit tests for removed classes
- [ ] Run `composer scan:all` (MANDATORY)
- [ ] Run `composer dt:run` (deptrac)
- [ ] Run `composer test:all` (full test suite)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| DI config (openapi-validator.php or similar) | CREATE | 1 |
| Slim middleware registration | MODIFY | 1 |
| `src/Presentation/Api/ApiAction.php` | MODIFY | 2 |
| DI config (http.php or similar) | MODIFY | 2 |
| `src/Infrastructure/Http/OpenApiRequestValidator.php` | DELETE | 3 |
| `src/Core/Http/RequestValidator.php` | DELETE | 3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Not Started | - | |
| 2 | Not Started | - | |
| 3 | Not Started | - | |
