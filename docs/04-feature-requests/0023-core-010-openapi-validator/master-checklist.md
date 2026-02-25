# Master Checklist: CORE-010 League OpenAPI Validator

> Task: bgl-dpk
> Created: 2026-02-23
> Depends on: bgl-aqk (CORE-009)

## Overview

**Overall Progress:** 3 of 3 stages completed
**Current Stage:** Done

---

## Stage 1: Install Package + OpenApi Spec Factory + Adapter

**Dependencies:** CORE-009 completed

- [x] Install `league/openapi-psr7-validator` + `devizzent/cebe-php-openapi`
- [x] Create DI factory `config/common/openapi-validator.php`:
  - Builds `cebe\openapi\spec\OpenApi` from merged config (ConfigAggregator)
  - Fixes version to 3.0.0, adds default `responses` to operations
  - Registers `League\OpenAPIValidation\PSR7\ServerRequestValidator`
- [x] Fix `config/common/openapi/v1.php`: version 1.0.0 -> 3.0.0
- [x] Create `src/Infrastructure/Http/LeagueRequestValidator.php` (adapter)
- [x] Verify: `composer lp:run && composer ps:run` -- passed

**Deviation from original plan:** Instead of middleware approach (SlimAdapter + ValidationMiddleware), used adapter pattern -- `LeagueRequestValidator` implements existing `RequestValidator` interface, delegating to League's `ServerRequestValidator`. This is simpler and avoids breaking changes to ApiAction.

---

## Stage 2: Wire DI + Unit Tests

**Dependencies:** Stage 1

- [x] Update `config/common/http.php`: swap `OpenApiRequestValidator` -> `LeagueRequestValidator`
- [x] Write unit tests `tests/Unit/Infrastructure/Http/LeagueRequestValidatorCest.php`:
  - Valid request passes through
  - Missing required field returns error
  - Invalid email format returns error
  - MinLength validation
  - Operation without request body passes
  - Unknown path skips validation (NoOperation handled gracefully)
  - Path with parameter passes
  - Type validation for string
- [x] Verify: `composer lp:run && composer ps:run && composer test:unit` -- all 171 tests pass

**Deviation from original plan:** AttributeInputValidator was not wired into ApiAction in this stage because it was already independently available. The focus was on replacing the manual OpenAPI validation with League.

---

## Stage 3: Cleanup + Final Validation

**Dependencies:** Stage 2

- [x] Delete `src/Infrastructure/Http/OpenApiRequestValidator.php`
- [x] Delete `tests/Unit/Infrastructure/Http/OpenApiRequestValidatorCest.php`
- [x] `RequestValidator` interface kept (still used by ApiAction)
- [x] Remove unused `ext-ctype` dependency (was only used by deleted validator)
- [x] Fix pre-existing Psalm errors in `OpenApiExportCommand.php`
- [x] Run `composer scan:all` -- passed (deptrac has pre-existing violations in Core\Validation\Attributes)
- [x] Run `composer test:unit` -- all 171 tests pass

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `config/common/openapi-validator.php` | CREATE | 1 |
| `src/Infrastructure/Http/LeagueRequestValidator.php` | CREATE | 1 |
| `config/common/openapi/v1.php` | MODIFY | 1 |
| `config/common/http.php` | MODIFY | 2 |
| `tests/Unit/Infrastructure/Http/LeagueRequestValidatorCest.php` | CREATE | 2 |
| `src/Infrastructure/Http/OpenApiRequestValidator.php` | DELETE | 3 |
| `tests/Unit/Infrastructure/Http/OpenApiRequestValidatorCest.php` | DELETE | 3 |
| `src/Presentation/Console/Commands/OpenApiExportCommand.php` | FIX | 3 |
| `composer.json` | MODIFY | 1,3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-02-26 | Package installed, adapter created, DI factory built |
| 2 | Done | 2026-02-26 | DI wired, 8 unit tests pass |
| 3 | Done | 2026-02-26 | Old code deleted, scan passes (deptrac pre-existing only) |
