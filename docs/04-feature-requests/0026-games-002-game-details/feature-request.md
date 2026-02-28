# Feature Request: Game Details (GAMES-002)

**Document Version:** 1.0
**Date:** 2026-02-28
**Status:** In Progress
**Priority:** P2

---

## 1. Feature Overview

### Description

GET /v1/games/{id}: retrieve game details by ID. If game exists in local DB, return it.
If not found locally, attempt to fetch from BGG by bggId and save. Returns all Game fields
plus optional user play statistics (totalPlays, lastPlayedAt) when authenticated.

### Business Value

- Users can view detailed information about any board game
- Foundation for game references in play sessions
- Leverages existing BGG integration for game discovery

### Target Users

- Board game enthusiasts browsing game information

---

## 2. Technical Architecture

### Approach

New GetGame Query + Handler. Handler uses Games repository (CompositeGames) to find by ID.
If not found by UUID, try findByBggId. Public endpoint with optional auth for user stats.

Since Play sessions don't yet have gameId (being added in PLAYS-001), userStats will return
zeros initially and become populated as sessions reference games.

### Integration Points

- Games repository (CompositeGames: local + BGG)
- Plays repository (for user stats, optional)
- AuthInterceptor (optional, for user-specific stats)

### Dependencies

- GAMES-001: Game Search via BGG (completed)

---

## 3. API Specification

| Method | Path             | Auth     | Description    |
|--------|------------------|----------|----------------|
| GET    | `/v1/games/{id}` | Optional | Get game details |

### Response (200)

```json
{
    "code": 0,
    "data": {
        "id": "550e8400-...",
        "bgg_id": 174430,
        "name": "Gloomhaven",
        "year_published": 2017
    }
}
```

### Errors

- 404 Not Found: game not found by ID
- 500 Internal Error: BGG API unavailable

---

## 4. Directory Structure

```
src/
    Application/Handlers/Games/GetGame/
        Query.php                      # CREATE
        Handler.php                    # CREATE
        Result.php                     # CREATE

    config/
        common/openapi/games.php       # MODIFY: add GET /v1/games/{id}
        common/bus.php                 # MODIFY: register GetGame handler
        _serialise-mapping.php         # MODIFY: add GetGame Result
```

---

## 5. Testing Strategy

### Functional Tests (Main Focus)

- Handler: find existing game by ID returns Result
- Handler: game not found throws DomainException

### Acceptance Tests (Web)

- GET /v1/games/{id} with valid ID returns 200
- GET /v1/games/{id} with non-existent ID returns 404

---

## 6. Acceptance Criteria

- [ ] GetGame Query with gameId parameter
- [ ] GetGame Handler using Games repository
- [ ] GetGame Result with all Game fields
- [ ] OpenAPI config for GET /v1/games/{id}
- [ ] Serialization mapping for GetGame Result
- [ ] Bus registration for GetGame handler
- [ ] Functional tests for handler
- [ ] Acceptance tests for HTTP endpoint
- [ ] `composer scan:all` passes
