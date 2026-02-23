# Feature Request: CORE-010 League OpenAPI Validator

**Task:** bgl-dpk
**Date:** 2026-02-23
**Status:** Open
**Priority:** P2
**Depends on:** bgl-aqk (CORE-009)

---

## 1. Feature Overview

### Description

Replace custom OpenApiRequestValidator (310 lines) with `league/openapi-psr7-validator` PSR-15 middleware. Convert PHP-array OpenAPI config to `cebe\openapi\spec\OpenApi` object at boot. Wire existing `AttributeInputValidator` into ApiAction for message-level validation (two-level validation: HTTP schema + message attributes).

### Business Value

- 310 lines of custom validation code replaced by maintained OSS package
- PSR-15 middleware: standard, composable, testable
- Two-level validation: schema (League) + business rules (AttributeInputValidator)
- Typed exceptions: `InvalidBody`, `InvalidHeaders`, `InvalidQueryArgs`

### Motivation

Current `OpenApiRequestValidator` (310 lines) manually extracts body schema, validates required fields, checks types -- duplicating what `league/openapi-psr7-validator` does automatically from OpenAPI spec.

---

## 2. Technical Architecture

### Approach

**Phase 1: Install package + boot-time schema**
- Install `league/openapi-psr7-validator` via composer
- DI factory: merge all OpenAPI PHP-array configs into `cebe\openapi\spec\OpenApi` object
- Register `ValidationMiddleware` via `SlimAdapter` in Slim middleware stack

**Phase 2: Wire AttributeInputValidator**
- In ApiAction: after hydrating Command, call `AttributeInputValidator::validate($command)`
- If validation fails, return 422 with structured errors

**Phase 3: Cleanup**
- Delete `OpenApiRequestValidator`
- Remove `RequestValidator` interface if no longer needed
- Update DI configs

### Integration Points

- Slim middleware stack (new PSR-15 validation middleware)
- `src/Presentation/Api/ApiAction.php` -- remove RequestValidator, add AttributeInputValidator
- DI container config

### Packages

- `league/openapi-psr7-validator` (ADR-011) -- PSR-15 OpenAPI validation middleware

---

## 3. Directory Structure

```
config/common/
    openapi-validator.php       # DI: OpenApi spec factory + middleware registration

src/Presentation/Api/
    ApiAction.php               # Remove RequestValidator, add AttributeInputValidator call
```

### Files to delete

```
src/Infrastructure/Http/OpenApiRequestValidator.php
src/Core/Http/RequestValidator.php    # If no longer needed
```

---

## 4. Testing Strategy

### Unit Tests

- Verify League middleware rejects invalid body (missing required, wrong type)
- Verify AttributeInputValidator runs after hydration
- Verify valid requests pass both levels

### Existing Tests

- E2E tests in `tests/Web/` -- must stay green
- Add negative E2E tests for validation errors

---

## 5. Acceptance Criteria

- [ ] `league/openapi-psr7-validator` installed
- [ ] PHP-array OpenAPI config converted to `cebe\openapi\spec\OpenApi` at boot
- [ ] PSR-15 validation middleware registered in Slim stack
- [ ] `AttributeInputValidator` wired in ApiAction after hydration
- [ ] `OpenApiRequestValidator` deleted
- [ ] `RequestValidator` interface removed (if unused)
- [ ] Two-level validation works: schema + attributes
- [ ] `composer scan:all` passes
- [ ] `composer test:web` passes
