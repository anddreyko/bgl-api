# Documentation: Token Refresh

> FR: 0021-auth-003-token-refresh
> Completed: 2026-02-23

## Summary

Implemented token refresh endpoint that accepts refresh token in request body, validates signature, verifies user is active, and returns new access/refresh token pair. Public endpoint (no AuthInterceptor required).

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/Auth/RefreshToken/Command.php` | Command with refreshToken property |
| `src/Application/Handlers/Auth/RefreshToken/Handler.php` | Validates refresh token and generates new pair |
| `src/Application/Handlers/Auth/RefreshToken/Result.php` | Result with access/refresh tokens and TTL |
| `tests/Unit/Application/Handlers/Auth/RefreshToken/HandlerCest.php` | Unit tests |
| `config/common/openapi/auth.php` | POST /v1/auth/refresh endpoint definition |

## How It Works

Token refresh flow:
1. Client sends POST /v1/auth/refresh with refreshToken in body
2. Handler verifies refresh token signature using TokenGenerator
3. Handler extracts userId and type from token payload
4. Handler validates type is 'refresh' (401 if not)
5. Handler loads User by userId (401 if not found)
6. Handler checks User status is Active (401 if Inactive)
7. Handler generates new access token with 2h TTL
8. Handler generates new refresh token with 30d TTL
9. Handler returns Result with both tokens and expiresIn

This is a public endpoint (no Bearer token required in header). The refresh token is sent in the request body and validated there.

Token rotation:
- Each refresh request generates a NEW refresh token
- Old refresh token should be discarded by client
- This improves security by limiting refresh token lifetime

Error responses:
- 401 Unauthorized: Invalid or expired refresh token
- 401 Unauthorized: Token type is not 'refresh'
- 401 Unauthorized: User not found or not Active

## Testing

Tests cover:
- Successful refresh with valid refresh token
- Invalid refresh token rejection
- Expired refresh token rejection
- Wrong token type rejection (access token passed instead)
- Inactive user rejection
- New token pair generation
