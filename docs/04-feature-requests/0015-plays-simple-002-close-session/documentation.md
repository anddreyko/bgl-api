# Documentation: Close Game Session

> FR: 0015-plays-simple-002-close-session
> Completed: 2026-02-23

## Summary

Implemented endpoint to close open game plays. Allows updating start/finish times and validates play ownership. Protected endpoint requiring valid Bearer token.

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/Plays/ClosePlay/Command.php` | Command to close play with optional time fields |
| `src/Application/Handlers/Plays/ClosePlay/Handler.php` | Loads play, validates ownership, closes play |
| `src/Application/Handlers/Plays/ClosePlay/Result.php` | Result with play times |
| `tests/Unit/Application/Handlers/Plays/CloseSession/HandlerCest.php` | Unit tests |
| `config/common/openapi/plays.php` | PATCH /v1/plays/sessions/{id} endpoint definition |

## How It Works

1. Client sends PATCH /v1/plays/sessions/{id} with Bearer token
2. AuthInterceptor validates token and extracts userId
3. Handler receives Command with:
   - playId from path parameter
   - userId from authentication context
   - Optional startedAt and finishedAt from request body
4. Handler loads Play entity from repository
5. Handler validates play belongs to authenticated user (403 if not)
6. If finishedAt not provided, uses current server time
7. Handler calls `Play::close()` method to update status and finishedAt
8. Handler saves updated play
9. Handler returns Result with playId and times

Security:
- Play ownership validated (userId must match)
- Already closed plays return 409 Conflict
- Non-existent plays return 404

## Testing

Tests cover:
- Handler closes open play successfully
- Handler rejects already closed play
- Handler rejects play owned by different user
- Handler uses default finished_at when not provided
- Full persistence: open play, then close it
