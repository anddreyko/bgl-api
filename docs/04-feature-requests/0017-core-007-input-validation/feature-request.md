# Feature Request: CORE-007 Input Validation

**Task:** bgl-z14
**Date:** 2026-02-23
**Status:** Completed
**Priority:** P0

---

## 1. Feature Overview

### Description

Declarative input validation using custom PHP attributes in Core layer. Validation attributes (NotBlank, ValidEmail, MinLength, ValidUuid) define business-level input constraints. AttributeInputValidator in Infrastructure reads attributes via Reflection and produces structured validation errors.

### Business Value

- Declarative, reusable validation rules as PHP attributes
- Separation: Core defines contracts, Infrastructure implements
- Works alongside OpenAPI schema-level validation (Level 1 vs Level 2)

---

## 2. Technical Architecture

### Approach

- Validation attributes as PHP 8+ attributes in `Core/Validation/Attributes/`
- `InputValidator` interface in `Core/Validation/`
- `AttributeInputValidator` in `Infrastructure/Validation/` uses Reflection to read attributes
- Returns structured `ValidationResult` with field-level errors

### Integration Points

- Used by handlers to validate Command/Query objects
- Complements OpenAPI middleware (Level 1: schema, Level 2: attributes)

---

## 3. Directory Structure

```
src/Core/Validation/
    InputValidator.php              # Interface
    ValidationResult.php            # Value object: errors collection
    Attributes/
        NotBlank.php                # String not empty
        ValidEmail.php              # Valid email format
        MinLength.php               # Minimum string length
        ValidUuid.php               # Valid UUID v4 format

src/Infrastructure/Validation/
    AttributeInputValidator.php     # Reflection-based implementation

config/common/validation.php        # DI bindings
```

---

## 4. Testing Strategy

### Unit Tests

- Each attribute: valid/invalid cases
- AttributeInputValidator: processes attributes on test DTO
- ValidationResult: error accumulation

---

## 5. Acceptance Criteria

- [ ] InputValidator interface in Core
- [ ] ValidationResult value object
- [ ] 4 validation attributes: NotBlank, ValidEmail, MinLength, ValidUuid
- [ ] AttributeInputValidator reads attributes via Reflection
- [ ] DI config registers InputValidator binding
- [ ] Unit tests for all attributes and validator
- [ ] `composer scan:all` passes
