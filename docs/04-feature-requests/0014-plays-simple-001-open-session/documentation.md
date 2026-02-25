# Documentation: Open Game Session

> FR: 0014-plays-simple-001-open-session
> Completed: 2026-02-23

## Summary

Implemented endpoint to create new game sessions for authenticated users. Accepts optional name and started_at fields, returns created session ID. This is the core feature enabling users to track board game sessions.

## Key Files

| File | Purpose |
|------|---------|
| `src/Domain/Plays/Entities/Play.php` | Play entity with open/close lifecycle |
| `src/Domain/Plays/Entities/PlayStatus.php` | Play status enum (Open, Closed) |
| `src/Domain/Plays/Entities/Plays.php` | Play repository interface |
| `src/Infrastructure/Persistence/Doctrine/Plays/Plays.php` | Doctrine repository implementation |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Plays/PlayMapping.php` | ORM mapping for Play entity |
| `src/Application/Handlers/Plays/OpenSession/Command.php` | Command to open session |
| `src/Application/Handlers/Plays/OpenSession/Handler.php` | Handler creates session and persists |
| `src/Application/Handlers/Plays/OpenSession/Result.php` | Result with sessionId |
| `tests/Unit/Application/Handlers/Plays/OpenSession/HandlerCest.php` | Unit tests |
| `tests/Unit/Domain/Plays/Entities/PlayCest.php` | Entity tests |
| `config/common/openapi/plays.php` | POST /v1/plays/sessions endpoint definition |

## How It Works

1. Client sends POST /v1/plays/sessions with Bearer token
2. AuthInterceptor validates token and extracts userId
3. Handler receives Command with userId (from auth), optional name and startedAt (from body)
4. Handler creates Play entity via `Play::open()` factory method
5. If startedAt not provided, uses current server time
6. If name not provided, defaults to empty string
7. Play saved to database via Plays repository
8. Handler returns Result with generated play ID

Database schema:
- Table: `plays_session`
- Fields: id (UUID), user_id (UUID), name (VARCHAR), status (INT), started_at (TIMESTAMP), finished_at (TIMESTAMP)
- Indexes: user_id, started_at DESC

## Testing

Tests cover:
- Play entity creation with defaults
- PlayStatus enum values
- Handler creates session with provided data
- Handler creates session with defaults when fields missing
- Repository persistence round-trip
