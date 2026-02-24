# Documentation: Input Validation with Attributes

> FR: 0017-core-007-input-validation
> Completed: 2026-02-23

## Summary

Implemented declarative input validation using custom PHP attributes. Validation attributes (NotBlank, ValidEmail, MinLength, ValidUuid) define business-level input constraints. AttributeInputValidator uses Reflection to read attributes and produce structured validation errors.

## Key Files

| File | Purpose |
|------|---------|
| `src/Core/Validation/InputValidator.php` | Contract interface for validation |
| `src/Core/Validation/ValidationResult.php` | Value object holding validation errors |
| `src/Core/Validation/Attributes/NotBlank.php` | Attribute for non-empty strings |
| `src/Core/Validation/Attributes/ValidEmail.php` | Attribute for email format validation |
| `src/Core/Validation/Attributes/MinLength.php` | Attribute for minimum string length |
| `src/Core/Validation/Attributes/ValidUuid.php` | Attribute for UUID v4 format validation |
| `src/Infrastructure/Validation/AttributeInputValidator.php` | Reflection-based validator implementation |
| `tests/Unit/Core/Validation/Attributes/*Cest.php` | Tests for each attribute |
| `tests/Unit/Infrastructure/Validation/AttributeInputValidatorCest.php` | Validator tests |
| `config/common/validation.php` | DI configuration |

## How It Works

This is Level 2 validation (business rules), complementing Level 1 (OpenAPI schema validation):

1. Define validation rules as PHP attributes on Command/Query constructor parameters:
```php
#[NotBlank]
#[ValidEmail]
private string $email;

#[NotBlank]
#[MinLength(8)]
private string $password;
```

2. AttributeInputValidator reads attributes via Reflection
3. Validates each property value against its attributes
4. Returns ValidationResult with field-level errors

Validation attributes:
- NotBlank: String must not be empty
- ValidEmail: Valid email format
- MinLength: Minimum string length (constructor param: int $min)
- ValidUuid: Valid UUID v4 format

ValidationResult:
- `hasErrors(): bool` - Check if validation failed
- `getErrors(): array` - Get field-level error messages

The validator is used by handlers to validate Command/Query objects before processing business logic.

## Testing

Tests cover:
- Each attribute with valid/invalid cases
- AttributeInputValidator processes attributes on test DTOs
- ValidationResult accumulates errors correctly
- Multiple attributes on single property
