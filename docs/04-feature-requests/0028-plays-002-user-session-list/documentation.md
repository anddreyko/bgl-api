# Documentation: User Session List (PLAYS-002)

**Completed:** 2026-03-01

---

## Summary

Implemented `GET /v1/plays/sessions` endpoint for paginated listing of user's play sessions with optional filters.

## API

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/v1/plays/sessions` | Required (Bearer) | List user's play sessions |

### Query Parameters

| Name | Type | Default | Description |
|------|------|---------|-------------|
| page | integer | 1 | Page number (min: 1) |
| size | integer | 20 | Page size (min: 1, max: 100) |
| game_id | string (uuid) | - | Filter by game |
| from | string (date-time) | - | Filter: startedAt >= |
| to | string (date-time) | - | Filter: startedAt <= |

### Response

```json
{
    "code": 0,
    "data": {
        "items": [
            {
                "id": "uuid",
                "name": "string|null",
                "status": "draft|published",
                "visibility": "private|link|participants|authenticated|public",
                "started_at": "ISO 8601",
                "finished_at": "ISO 8601|null",
                "game": { "id": "uuid", "name": "string" } | null,
                "players": [
                    { "id": "uuid", "mate_id": "uuid", "score": int|null, "is_winner": bool, "color": "string|null" }
                ]
            }
        ],
        "total": 15,
        "page": 1,
        "size": 20
    }
}
```

## Architecture

- **Handler:** `src/Application/Handlers/Plays/ListPlays/Handler.php`
- **Query:** `src/Application/Handlers/Plays/ListPlays/Query.php`
- **Result:** `src/Application/Handlers/Plays/ListPlays/Result.php`

Uses existing `Searchable` interface with `Filter` objects (`Equals`, `Greater`, `Less`, `AndX`) for query building. No custom repository methods added.

## Files Changed

| File | Action |
|------|--------|
| `src/Application/Handlers/Plays/ListPlays/Query.php` | Created |
| `src/Application/Handlers/Plays/ListPlays/Handler.php` | Created |
| `src/Application/Handlers/Plays/ListPlays/Result.php` | Created |
| `config/common/bus.php` | Modified |
| `config/_serialise-mapping.php` | Modified |
| `config/common/openapi/plays.php` | Modified |
| `tests/Functional/Plays/ListPlaysCest.php` | Created |
| `tests/Web/PlaySessionCest.php` | Modified |

## Test Coverage

### Functional (9 tests)

- testListPlaysReturnsUserSessions
- testListPlaysWithPagination
- testListPlaysFilterByGameId
- testListPlaysFilterByDateRange
- testListPlaysEmptyResult
- testListPlaysDoesNotShowOtherUserSessions
- testListPlaysSortedByStartedAtDesc
- testListPlaysIncludesGameInfo
- testListPlaysIncludesPlayerInfo

### Web Acceptance (2 tests)

- testListSessionsReturns200
- testListSessionsWithoutTokenReturns401

## Known Limitations

- N+1 queries: `search()` returns keys, then `find()` per entity. Tracked as separate task for DoctrineRepository refactoring.
