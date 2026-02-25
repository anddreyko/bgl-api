# Documentation: CORE-010 League OpenAPI Validator

> Task: bgl-dpk
> Completed: 2026-02-26

---

## Commit Message

```
chore(http): replace custom OpenAPI validator with league/openapi-psr7-validator
```

---

## What & Why

Replaced custom `OpenApiRequestValidator` (310 lines of manual schema validation) with `league/openapi-psr7-validator`
via adapter pattern. The custom validator manually extracted body schema, validated required fields, and checked
types -- all of which the League package handles automatically from the OpenAPI spec.

## Changes Made

**Infrastructure Layer:**

- Created `src/Infrastructure/Http/LeagueRequestValidator.php` -- adapter implementing `RequestValidator` interface,
  delegates to League's `ServerRequestValidator`
- Created `config/common/openapi-validator.php` -- DI factory that builds `cebe\openapi\spec\OpenApi` from merged
  PHP-array configs
- Deleted `src/Infrastructure/Http/OpenApiRequestValidator.php` (310 lines replaced)

**Configuration:**

- Updated `config/common/http.php` -- swapped `OpenApiRequestValidator` for `LeagueRequestValidator` in DI
- Fixed `config/common/openapi/v1.php` -- version 1.0.0 to 3.0.0 for spec compliance

## Technical Details

**Patterns Used:**

- Adapter Pattern: `LeagueRequestValidator` wraps League's `ServerRequestValidator` behind existing `RequestValidator`
  interface
- No breaking changes to `ApiAction` or any consumers

**Key Decisions:**

- Kept `RequestValidator` interface (still used by `ApiAction`) instead of removing it
- Used adapter pattern instead of PSR-15 middleware approach -- simpler, no changes to middleware stack
- `NoOperation` exceptions (unknown paths) are silently skipped -- ApiAction handles 404 separately

**Error Extraction:**

- `ValidationFailed` exceptions are parsed recursively via `KeywordMismatch` breadcrumbs
- Returns structured `array<string, string[]>` (field -> error messages)

## Testing

**Automated Tests:**

- `tests/Unit/Infrastructure/Http/LeagueRequestValidatorCest.php` -- 8 unit tests:
    - Valid request passes
    - Missing required field
    - Invalid email format
    - MinLength validation
    - Operation without request body
    - Unknown path (NoOperation)
    - Path with parameters
    - Type validation

## Related Files

| File                                                            | Description                             |
|-----------------------------------------------------------------|-----------------------------------------|
| `src/Infrastructure/Http/LeagueRequestValidator.php`            | Adapter for League validator            |
| `config/common/openapi-validator.php`                           | DI factory for OpenApi spec + validator |
| `config/common/http.php`                                        | DI wiring                               |
| `tests/Unit/Infrastructure/Http/LeagueRequestValidatorCest.php` | Unit tests                              |

## Dependencies Added

- `league/openapi-psr7-validator`
- `devizzent/cebe-php-openapi`
