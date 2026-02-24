# Master Checklist: CORE-008 Token Generator

**Task:** bgl-6si
**Size:** Small
**Approach:** TDD (Red -> Green -> Refactor)

---

## Stage 1: Contract + Tests (Red)

- [x] Create `src/Core/Security/TokenGenerator.php` interface
  - `generate(array $payload, int $ttlSeconds): string`
  - `verify(string $token): array`
- [x] Create `tests/Unit/Infrastructure/Security/JwtTokenGeneratorCest.php`
  - Test: generate returns non-empty string
  - Test: verify returns original payload
  - Test: expired token throws exception
  - Test: tampered token throws exception
- [x] Create `tests/Unit/Infrastructure/Security/PlainTokenGeneratorCest.php`
  - Test: generate + verify roundtrip
  - Test: expired token throws exception
- [x] Run tests -- confirm they FAIL (Red)

## Stage 2: Implementation (Green)

- [x] Install `lcobucci/jwt` via `docker compose run --rm api-php-cli composer require lcobucci/jwt`
- [x] Create `src/Infrastructure/Security/JwtTokenGenerator.php`
  - HMAC SHA-256 signing
  - Claims: `sub` from payload, `iat`, `exp`, `nbf`
  - Inject `ClockInterface` for time
  - Inject `string $secret` for signing key
- [x] Create `src/Infrastructure/Security/PlainTokenGenerator.php`
  - Base64-encode JSON `{payload, exp}`
  - Verify: base64-decode, check exp against clock
- [x] Run tests -- confirm they PASS (Green)

## Stage 3: DI Config + Quality Gates

- [x] Update `config/common/security.php` -- add TokenGenerator -> JwtTokenGenerator binding
  - Use `JWT_SECRET` env variable
- [x] Run `composer lp:run` -- passes
- [x] Run `composer ps:run` -- passes
- [x] Run `composer dt:run` -- passes
- [x] Run `composer test:unit` -- passes

## Validation Criteria

- TokenGenerator interface follows PasswordHasher pattern (same directory, same style)
- JwtTokenGenerator uses lcobucci/jwt, not firebase/php-jwt
- PlainTokenGenerator has zero crypto dependencies
- All tests pass, scan passes
