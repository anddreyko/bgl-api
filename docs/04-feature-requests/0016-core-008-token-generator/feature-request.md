# Feature Request: Token Generator Contract and Component

**Document Version:** 1.0
**Date:** 2026-02-22
**Status:** Completed
**Priority:** P1 (Core, Phase 0)

---

## 1. Feature Overview

### Description

Token Generator contract (`Core/Security/TokenGenerator`) with two implementations:
- `JwtTokenGenerator` (Infrastructure) for production use with HMAC SHA-256 signing via lcobucci/jwt
- `PlainTokenGenerator` (Infrastructure/tests) for testing -- base64-encoded JSON, no cryptographic overhead

### Business Value

- Foundation for authentication (login returns JWT token)
- Testability: PlainTokenGenerator eliminates JWT overhead in tests
- Decoupled from specific JWT library via Ports & Adapters

### Target Users

- Backend Developers: generating and verifying tokens in auth handlers
- QA Engineers: using PlainTokenGenerator in test suites

---

## 2. Technical Architecture

### Approach

Follow the same Ports & Adapters pattern as PasswordHasher:
- Port: `Core/Security/TokenGenerator` interface
- Adapter: `Infrastructure/Security/JwtTokenGenerator`
- Test adapter: `Infrastructure/Security/PlainTokenGenerator`

### Contract

```php
interface TokenGenerator
{
    /** @param array<string, mixed> $payload */
    public function generate(array $payload, int $ttlSeconds): string;

    /** @return array<string, mixed> */
    public function verify(string $token): array;
}
```

### Dependencies

- `lcobucci/jwt` -- JWT signing and verification
- `psr/clock` -- already installed, for time-based validation

---

## 3. Directory Structure

```
src/Core/Security/
    TokenGenerator.php              # Contract

src/Infrastructure/Security/
    JwtTokenGenerator.php           # Production: lcobucci/jwt
    PlainTokenGenerator.php         # Tests: base64 JSON

config/common/
    security.php                    # DI config (update existing)

tests/Unit/Infrastructure/Security/
    JwtTokenGeneratorCest.php       # Unit tests
    PlainTokenGeneratorCest.php     # Unit tests
```

---

## 4. Acceptance Criteria

- [ ] `TokenGenerator` interface in `Core/Security/`
- [ ] `JwtTokenGenerator` with HMAC SHA-256 via lcobucci/jwt
- [ ] `PlainTokenGenerator` with base64 JSON encoding
- [ ] Contract tests: generate + verify roundtrip, expired token, tampered token
- [ ] DI config wires `TokenGenerator` to `JwtTokenGenerator`
- [ ] `JWT_SECRET` env variable used for signing key
- [ ] `composer scan:all` passes
