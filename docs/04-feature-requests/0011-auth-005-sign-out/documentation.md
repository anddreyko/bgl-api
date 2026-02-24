# Documentation: Sign Out

> FR: 0011-auth-005-sign-out
> Completed: 2026-02-23

## Summary

Implemented sign-out endpoint for authenticated users. This is a client-side logout: the server returns success, and the client deletes stored tokens. No server-side token blacklist (JWT stateless architecture).

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/Auth/SignOut/Command.php` | Sign-out command with userId |
| `src/Application/Handlers/Auth/SignOut/Handler.php` | Returns success message |
| `tests/Unit/Application/Handlers/Auth/SignOut/HandlerCest.php` | Unit tests |
| `config/common/openapi/auth.php` | POST /v1/auth/sign-out endpoint definition |

## How It Works

1. Client sends POST /v1/auth/sign-out with Bearer token
2. AuthInterceptor validates token and extracts userId
3. Handler receives Command with userId
4. Handler returns success message
5. Client deletes stored access and refresh tokens

This provides a clean API contract for logout and allows for future server-side cleanup if needed (e.g., token blacklist, session cleanup).

## Testing

Unit tests cover:
- Handler returns success for valid userId
- Handler works with authenticated context
