# Documentation: OpenAPI Request Validation

> FR: 0009-openapi-validation-middleware
> Completed: 2026-02-23

## Summary

Implemented OpenAPI-based request validation through the `OpenApiRequestValidator` component, integrated into the HTTP pipeline. Validates request bodies, query parameters, and path parameters against OpenAPI schema definitions (type, format, required, min/max length, enums).

## Key Files

| File | Purpose |
|------|---------|
| `src/Core/Http/RequestValidator.php` | Contract interface for request validation |
| `src/Infrastructure/Http/OpenApiRequestValidator.php` | OpenAPI schema-based validation implementation |
| `config/common/http.php` | DI configuration for validator |
| `tests/Unit/Infrastructure/Http/OpenApiRequestValidatorCest.php` | Unit tests for validation logic |

## How It Works

The `OpenApiRequestValidator` reads OpenAPI operation definitions and validates:

1. Request body validation:
   - Required fields must be present
   - Field types (string, integer, number, boolean, array, object)
   - Formats (email, uuid, date, date-time, url)
   - String constraints (minLength, maxLength)
   - Numeric constraints (minimum, maximum)
   - Enum values

2. Parameter validation:
   - Query parameters
   - Path parameters
   - Required parameter checks
   - Type and format validation

The validator is integrated into the HTTP pipeline and automatically validates requests based on OpenAPI route definitions in `config/common/openapi/`.

## Testing

Unit tests cover:
- Valid requests pass through
- Missing required fields return validation errors
- Type mismatches return validation errors
- Format validation (email, uuid, date-time)
- String length constraints
- Numeric range constraints
- Enum validation
