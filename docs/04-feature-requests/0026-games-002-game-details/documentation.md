# Documentation: Game Details (GAMES-002)

> Completed: 2026-03-01

## Summary

Added a GET endpoint to retrieve board game details by ID. The endpoint returns game metadata
(name, BGG ID, year published) from the local database. If the game is not found, a 404 response
is returned. This builds on the existing Games bounded context established by GAMES-001 (Game Search).

## API Endpoints

### GET /v1/games/{id}

Retrieves a single game by its UUID.

**Parameters:**

- `id` (path, required, string, uuid) -- the game's internal UUID

**Responses:**

- `200 OK` -- game found, returns game data
- `404 Not Found` -- game does not exist
- `500 Internal Error` -- server error

**Response body (200):**

```json
{
    "code": 0,
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "bgg_id": 174430,
        "name": "Gloomhaven",
        "year_published": 2017
    }
}
```

**OpenAPI configuration:** `config/common/openapi/games.php`

- `x-message`: `GetGame\Query::class`
- `x-map`: `['id' => 'gameId']` (maps path parameter to query field)

## Application Layer

### Query (`src/Application/Handlers/Games/GetGame/Query.php`)

Message object carrying a single `gameId` (non-empty-string). Implements `Message<Result>`.

### Handler (`src/Application/Handlers/Games/GetGame/Handler.php`)

Receives the query via `Envelope`, looks up the game through the `Games` repository interface.
Throws `NotFoundException` if no game is found. Returns a `Result` DTO with all game fields.

### Result (`src/Application/Handlers/Games/GetGame/Result.php`)

Readonly DTO with fields: `id` (string), `bggId` (int), `name` (string), `yearPublished` (?int).

### Serialization

Mapping in `config/_serialise-mapping.php` converts `Result` to JSON with snake_case keys:
`id`, `bgg_id`, `name`, `year_published`.

### Bus Registration

Handler registered in `config/common/bus.php` as `[GetGame\Query::class, GetGame\Handler::class]`.

## Files Created/Modified

| File | Action |
|------|--------|
| `src/Application/Handlers/Games/GetGame/Query.php` | Created |
| `src/Application/Handlers/Games/GetGame/Handler.php` | Created |
| `src/Application/Handlers/Games/GetGame/Result.php` | Created |
| `config/common/bus.php` | Modified -- added GetGame handler registration |
| `config/common/openapi/games.php` | Modified -- added GET /v1/games/{id} route |
| `config/_serialise-mapping.php` | Modified -- added GetGame\Result mapping |
| `tests/Functional/Games/GetGameCest.php` | Created |
| `tests/Web/GamesCest.php` | Modified -- added GetGame test methods |

## Test Coverage

### Functional Tests (`tests/Functional/Games/GetGameCest.php`)

- `testGetGameReturnsResult` -- creates a game via InMemory repository, invokes handler, asserts all Result fields match
- `testGetGameNotFoundThrowsException` -- invokes handler with non-existent ID, asserts `NotFoundException` is thrown

### Web Acceptance Tests (`tests/Web/GamesCest.php`)

- `testGetGameNotFoundReturns404` -- sends GET to `/v1/games/{uuid}` with a non-existent UUID, asserts HTTP 404
- `testGetGameReturns200` -- inserts a game row into the database, sends GET, asserts HTTP 200 and validates JSON structure (id, bgg_id, name, year_published)
