# Documentation: Close Game Session

> FR: 0015-plays-simple-002-close-session
> Completed: 2026-02-23

## Summary

Implemented endpoint to close open game sessions. Allows updating start/finish times and validates session ownership. Protected endpoint requiring valid Bearer token.

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/Plays/CloseSession/Command.php` | Command to close session with optional time fields |
| `src/Application/Handlers/Plays/CloseSession/Handler.php` | Loads session, validates ownership, closes session |
| `src/Application/Handlers/Plays/CloseSession/Result.php` | Result with session times |
| `tests/Unit/Application/Handlers/Plays/CloseSession/HandlerCest.php` | Unit tests |
| `config/common/openapi/plays.php` | PATCH /v1/plays/sessions/{id} endpoint definition |

## How It Works

1. Client sends PATCH /v1/plays/sessions/{id} with Bearer token
2. AuthInterceptor validates token and extracts userId
3. Handler receives Command with:
   - sessionId from path parameter
   - userId from authentication context
   - Optional startedAt and finishedAt from request body
4. Handler loads Session entity from repository
5. Handler validates session belongs to authenticated user (403 if not)
6. If finishedAt not provided, uses current server time
7. Handler calls `Session::close()` method to update status and finishedAt
8. Handler saves updated session
9. Handler returns Result with sessionId and times

Security:
- Session ownership validated (userId must match)
- Already closed sessions return 409 Conflict
- Non-existent sessions return 404

## Testing

Tests cover:
- Handler closes open session successfully
- Handler rejects already closed session
- Handler rejects session owned by different user
- Handler uses default finished_at when not provided
- Full persistence: open session, then close it
