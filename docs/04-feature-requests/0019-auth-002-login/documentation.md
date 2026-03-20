# Documentation: Authentication (Login)

> FR: 0019-auth-002-login
> Completed: 2026-02-23

## Summary

Implemented email and password login endpoint returning JWT access/refresh token pair. Refactored existing LoginByCredentials stub handler into full implementation. User must have Active status to login.

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/Auth/LoginByCredentials/Command.php` | Rewritten as concrete class with email/password properties |
| `src/Application/Handlers/Auth/LoginByCredentials/Handler.php` | Rewritten with full authentication logic |
| `src/Application/Handlers/Auth/LoginByCredentials/Result.php` | Result with access/refresh tokens and TTL |
| `tests/Unit/Application/Handlers/Auth/LoginByCredentials/HandlerCest.php` | Unit tests |
| `config/common/openapi/auth.php` | POST /v1/auth/password/sign-in endpoint definition |

## How It Works

Login flow:
1. Client sends POST /v1/auth/password/sign-in with email and password
2. Handler finds User by email (401 if not found)
3. Handler verifies password using PasswordHasher (401 if wrong)
4. Handler checks User status is Active (403 if Inactive)
5. Handler generates access token with 2h TTL (7200 seconds)
6. Handler generates refresh token with 30d TTL (2592000 seconds)
7. Handler returns Result with both tokens and expiresIn

JWT token payload:
- userId: User UUID
- type: 'access' or 'refresh'
- Standard JWT claims (iat, exp, nbf) added by TokenGenerator

Token TTLs:
- Access token: 7200 seconds (2 hours)
- Refresh token: 2592000 seconds (30 days)

Error responses:
- 401 Unauthorized: Wrong credentials (email not found or password mismatch)
- 403 Forbidden: Unconfirmed email (User status not Active)

## Testing

Tests cover:
- Successful login with valid credentials
- Wrong password rejection
- Non-existent email rejection
- Inactive user rejection
- Token generation with correct payload
