# Feature Request: User Session List (PLAYS-002)

**Document Version:** 1.0
**Date:** 2026-03-01
**Status:** Done
**Priority:** P1

---

## 1. Feature Overview

### Description

GET /v1/plays/sessions: paginated list of user's play sessions with optional filters.
Returns items with embedded game name and players list. Auth required (user sees only own sessions).

### Business Value

- Core MVP: users browse their play history
- Foundation for statistics (STATS-001) and analytics
- Enables session management workflow (list -> view -> edit/delete)

### Target Users

- Board game enthusiasts reviewing their play history

---

## 2. Technical Architecture

### Approach

Follow ListMates pattern: add `findAllByUser()` and `countByUser()` to Plays repository.
Handler builds filter from query params, calls repository, transforms entities to arrays.

### Integration Points

- AuthInterceptor: userId from JWT
- Plays repository: new query methods with filters
- Games context: optional game name resolution via gameId
- Doctrine ORM: QueryBuilder with dynamic filters

### Dependencies

- PLAYS-001: Create Game Session (completed)

---

## 3. API Specification

| Method | Path                  | Auth     | Description         |
|--------|-----------------------|----------|---------------------|
| GET    | `/v1/plays/sessions`  | Required | List user sessions  |

### Query Parameters

| Name    | Type    | Required | Default | Description                      |
|---------|---------|----------|---------|----------------------------------|
| page    | integer | No       | 1       | Page number (min: 1)             |
| size    | integer | No       | 20      | Page size (min: 1, max: 100)     |
| game_id | string  | No       | -       | Filter by game UUID              |
| from    | string  | No       | -       | Filter: startedAt >= (ISO 8601)  |
| to      | string  | No       | -       | Filter: startedAt <= (ISO 8601)  |

### Response (200)

```json
{
    "code": 0,
    "data": {
        "items": [
            {
                "id": "550e8400-...",
                "name": "Friday Game Night",
                "status": "published",
                "visibility": "private",
                "started_at": "2026-02-28T19:00:00+00:00",
                "finished_at": "2026-02-28T22:00:00+00:00",
                "game": {
                    "id": "660e8400-...",
                    "name": "Catan"
                },
                "players": [
                    {
                        "id": "770e8400-...",
                        "mate_id": "880e8400-...",
                        "score": 42,
                        "is_winner": true,
                        "color": "blue"
                    }
                ]
            }
        ],
        "total": 15,
        "page": 1,
        "size": 20
    }
}
```

### Errors

- 401 Unauthorized: missing/invalid token
- 500 Internal Server Error

---

## 4. Directory Structure

```
src/
    Application/Handlers/Plays/ListPlays/
        Query.php               # CREATE
        Handler.php             # CREATE
        Result.php              # CREATE

    Domain/Plays/Entities/
        Plays.php               # MODIFY: add findAllByUser, countByUser

    Infrastructure/Persistence/Doctrine/Mapping/Plays/
        Plays.php               # MODIFY: implement findAllByUser, countByUser

    Infrastructure/Persistence/InMemory/
        InMemoryPlays.php       # MODIFY: implement findAllByUser, countByUser

config/
    common/openapi/plays.php    # MODIFY: add GET /v1/plays/sessions
    common/bus.php              # MODIFY: register ListPlays handler
    _serialise-mapping.php      # MODIFY: add ListPlays Result mapping
```

---

## 5. Code References

| File | Relevance |
|------|-----------|
| `src/Application/Handlers/Mates/ListMates/` | Pattern: paginated list handler |
| `src/Domain/Mates/Entities/Mates.php` | Pattern: findAllByUser + countByUser |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Mates/Mates.php` | Pattern: Doctrine QueryBuilder with user filter |
| `config/common/openapi/mates.php` | Pattern: OpenAPI list endpoint with pagination |
| `src/Domain/Plays/Entities/Play.php` | Entity to query |

---

## 6. Edge Cases

- No sessions: return empty items array with total=0
- Invalid date format in from/to: 400
- game_id not found: return empty (filter, not validation error)
- Page beyond total: return empty items with correct total
- Draft sessions: include in list (user owns them)

---

## 7. Testing Strategy

### Functional Tests (Main Focus)

- Handler: list sessions for user with multiple sessions
- Handler: pagination (page, size)
- Handler: filter by gameId
- Handler: filter by date range (from, to)
- Handler: empty result (no sessions)
- Handler: sessions from other users not visible

### Acceptance Tests (Web)

- GET /v1/plays/sessions returns 200 with correct structure
- GET /v1/plays/sessions without token returns 401
- GET /v1/plays/sessions with pagination params

---

## 8. Acceptance Criteria

- [ ] ListPlays Query + Handler + Result
- [ ] Plays repository: findAllByUser with filters, countByUser
- [ ] Doctrine implementation with QueryBuilder
- [ ] InMemory implementation for tests
- [ ] OpenAPI config for GET /v1/plays/sessions
- [ ] Serialization mapping for ListPlays Result
- [ ] Bus registration
- [ ] Functional tests for handler
- [ ] Web acceptance tests
- [ ] `composer scan:all` passes
