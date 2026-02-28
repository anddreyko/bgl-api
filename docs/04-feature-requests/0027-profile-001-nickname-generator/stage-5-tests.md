# Stage 5: Tests + Quality Gates

## Overview

Write all tests following Testing Trophy order, then run full quality gate suite.

## Dependencies

All previous stages (1-4) must be complete.

## Implementation Steps

### 5.1 Unit test: RandomNomenclator

File: `tests/Unit/Infrastructure/Identity/RandomNomenclatorCest.php`

Tests:
- `testGeneratesNonEmptyString` -- result is non-empty
- `testGeneratesValidFormat` -- matches pattern `/^[A-Z][a-z]+[A-Z][a-z]+\d*$/`
- `testGeneratesDifferentNames` -- 50 calls produce at least 5 unique values

### 5.2 Unit test: User::getName() regression

File: `tests/Unit/Domain/Profile/Entities/UserCest.php`

Tests:
- `testGetNameReturnsStableValue` -- call getName() twice, assert same result
- `testRegisterWithNameUsesProvidedName` -- register with explicit name, verify
- `testGetNameFallbackForNullName` -- construct User with null name, getName() returns 'Player'

### 5.3 Functional test: GenerateNickname Handler

File: `tests/Functional/Profile/GenerateNicknameCest.php`

Tests:
- `testHandlerReturnsNonEmptyString` -- invoke handler, check string result
- `testHandlerReturnsValidNicknameFormat` -- check format pattern

### 5.4 Update RegisterCest

File: `tests/Functional/Auth/RegisterCest.php`

Add test:
- `testRegistrationWithoutNameUsesGeneratedNickname` -- register without name, flush, reload user, assert name is not null and not 'User#NNNN'

Fix existing tests if `User::register()` signature changed (explicit name required).

### 5.5 Web test: API endpoint

File: `tests/Web/ProfileCest.php`

Tests:
- `testGenerateNameReturns200` -- POST /v1/profile/generate-name, assert 200
- `testGenerateNameReturnsValidJson` -- check `{"code": 0, "data": "..."}` structure
- `testGenerateNameReturnsNonEmptyName` -- data is non-empty string

### 5.6 Run quality gates

```bash
make scan
```

Fix any issues found.

## Files to Create/Modify

| File | Action |
|------|--------|
| `tests/Unit/Infrastructure/Identity/RandomNomenclatorCest.php` | CREATE |
| `tests/Unit/Domain/Profile/Entities/UserCest.php` | CREATE |
| `tests/Functional/Profile/GenerateNicknameCest.php` | CREATE |
| `tests/Functional/Auth/RegisterCest.php` | MODIFY |
| `tests/Web/ProfileCest.php` | CREATE |

## Completion Criteria

- All new tests pass
- All existing tests still pass
- `make scan` passes clean

## Verification

```bash
composer test:unit -- tests/Unit/Infrastructure/Identity/RandomNomenclatorCest.php
composer test:unit -- tests/Unit/Domain/Profile/Entities/UserCest.php
composer test:func -- tests/Functional/Profile/GenerateNicknameCest.php
composer test:func -- tests/Functional/Auth/RegisterCest.php
composer test:web -- tests/Web/ProfileCest.php
make scan
```

## Potential Issues

- RegisterCest creates User via `User::register()` without name (line 67-72) -- will need explicit name after Stage 2 changes
- Web tests require running app container -- ensure Docker is up
