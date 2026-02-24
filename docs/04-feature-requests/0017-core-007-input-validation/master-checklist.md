# Master Checklist: CORE-007 Input Validation

> Task: bgl-z14
> Created: 2026-02-23

## Overview

**Overall Progress:** 3 of 3 stages completed
**Current Stage:** Complete

---

## Stage 1: Core Contracts (~20min)

**Dependencies:** None

- [x] Create `src/Core/Validation/InputValidator.php` interface with `validate(object $target): ValidationResult`
- [x] Create `src/Core/Validation/ValidationResult.php` value object (errors array, hasErrors(), getErrors())
- [x] Create `src/Core/Validation/Attributes/NotBlank.php` attribute
- [x] Create `src/Core/Validation/Attributes/ValidEmail.php` attribute
- [x] Create `src/Core/Validation/Attributes/MinLength.php` attribute (constructor param: int $min)
- [x] Create `src/Core/Validation/Attributes/ValidUuid.php` attribute
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Infrastructure Implementation + Tests (~30min)

**Dependencies:** Stage 1

- [x] Create `src/Infrastructure/Validation/AttributeInputValidator.php`
  - Reads PHP attributes from constructor parameters of target object via Reflection
  - Validates each parameter value against its attributes
  - Returns ValidationResult with field-level errors
- [x] Create `tests/Unit/Core/Validation/Attributes/NotBlankCest.php`
- [x] Create `tests/Unit/Core/Validation/Attributes/ValidEmailCest.php`
- [x] Create `tests/Unit/Core/Validation/Attributes/MinLengthCest.php`
- [x] Create `tests/Unit/Core/Validation/Attributes/ValidUuidCest.php`
- [x] Create `tests/Unit/Infrastructure/Validation/AttributeInputValidatorCest.php`
- [x] Verify: `composer lp:run && composer ps:run && composer test:unit`

---

## Stage 3: DI Config + Final Validation (~10min)

**Dependencies:** Stage 2

- [x] Create `config/common/validation.php` with DI binding: InputValidator -> AttributeInputValidator
- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer dt:run` (architecture check)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Core/Validation/InputValidator.php` | CREATE | 1 |
| `src/Core/Validation/ValidationResult.php` | CREATE | 1 |
| `src/Core/Validation/Attributes/NotBlank.php` | CREATE | 1 |
| `src/Core/Validation/Attributes/ValidEmail.php` | CREATE | 1 |
| `src/Core/Validation/Attributes/MinLength.php` | CREATE | 1 |
| `src/Core/Validation/Attributes/ValidUuid.php` | CREATE | 1 |
| `src/Infrastructure/Validation/AttributeInputValidator.php` | CREATE | 2 |
| `tests/Unit/Core/Validation/Attributes/NotBlankCest.php` | CREATE | 2 |
| `tests/Unit/Core/Validation/Attributes/ValidEmailCest.php` | CREATE | 2 |
| `tests/Unit/Core/Validation/Attributes/MinLengthCest.php` | CREATE | 2 |
| `tests/Unit/Core/Validation/Attributes/ValidUuidCest.php` | CREATE | 2 |
| `tests/Unit/Infrastructure/Validation/AttributeInputValidatorCest.php` | CREATE | 2 |
| `config/common/validation.php` | CREATE | 3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Completed | 2026-02-23 | Core contracts and attributes |
| 2 | Completed | 2026-02-23 | Implementation and tests |
| 3 | Completed | 2026-02-23 | DI config and validation |
