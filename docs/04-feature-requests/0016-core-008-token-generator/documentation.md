# Documentation: Token Generator

> FR: 0016-core-008-token-generator
> Completed: 2026-02-23

## Summary

Implemented Token Generator contract with two implementations following Ports and Adapters pattern: JwtTokenGenerator for production use (HMAC SHA-256 via lcobucci/jwt) and PlainTokenGenerator for testing (base64-encoded JSON, no crypto overhead).

## Key Files

| File | Purpose |
|------|---------|
| `src/Core/Security/TokenGenerator.php` | Contract interface for token generation/verification |
| `src/Infrastructure/Security/JwtTokenGenerator.php` | Production JWT implementation with HMAC SHA-256 |
| `src/Infrastructure/Security/PlainTokenGenerator.php` | Test implementation with base64 JSON |
| `tests/Unit/Infrastructure/Security/JwtTokenGeneratorCest.php` | JWT implementation tests |
| `tests/Unit/Infrastructure/Security/PlainTokenGeneratorCest.php` | Plain implementation tests |
| `config/common/security.php` | DI configuration |

## How It Works

TokenGenerator interface:
- `generate(array $payload, int $ttlSeconds): string` - Generate token with payload and TTL
- `verify(string $token): array` - Verify token and return payload

JwtTokenGenerator (production):
- Uses lcobucci/jwt library
- HMAC SHA-256 signing algorithm
- JWT claims: sub (from payload), iat, exp, nbf
- Uses ClockInterface for time-based validation
- Signing key from JWT_SECRET environment variable

PlainTokenGenerator (testing):
- Base64-encodes JSON with payload and expiration
- No cryptographic overhead
- Fast for test suites
- Uses ClockInterface for expiration check

The token generator is used by authentication handlers (login, token refresh) to generate access and refresh token pairs.

## Testing

Tests cover:
- Generate returns valid token
- Verify returns original payload
- Expired token throws exception
- Tampered token throws exception (JWT only)
- Full generate + verify roundtrip
