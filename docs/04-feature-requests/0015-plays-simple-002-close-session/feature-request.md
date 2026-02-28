# Feature Request: Close Game Session (PLAYS-002)

**Document Version:** 1.0
**Date:** 2026-02-22
**Status:** Completed
**Priority:** P1 (Plays, Sprint 2)

---

## 1. Feature Overview

### Description

Close an open game play by setting the finish time and updating the status. Accepts optional `started_at`,
`finished_at`, and `interval` fields. Protected endpoint. The play must belong to the authenticated user.

### Business Value

- Complete play lifecycle: open -> close
- Users can record play duration
- Feature parity with main branch

### Target Users

- Board game enthusiasts finishing their plays

---

## 2. Technical Architecture

### Approach

Command + Handler pattern. Handler loads existing Play entity, validates ownership, updates fields, and saves.
Uses existing Plays repository from PLAYS-001.

### Integration Points

- AuthInterceptor: userId from JWT
- Plays repository: load and save play
- Play entity: close() method changes status and sets finishedAt

### Dependencies

- PLAYS-001: Session entity, repository, and migration must exist

---

## 3. Sequence Diagram

```mermaid
sequenceDiagram
    participant Client
    participant ApiAction
    participant AuthInterceptor
    participant Handler as ClosePlay Handler
    participant Repo as Plays Repository

    Client->>ApiAction: PATCH /v1/plays/sessions/{id} (Bearer token)
    ApiAction->>AuthInterceptor: validate token
    AuthInterceptor->>ApiAction: userId
    ApiAction->>Handler: ClosePlay Command(playId, userId, fields)
    Handler->>Repo: getById(playId)
    Repo-->>Handler: Play entity
    Handler->>Handler: validate ownership, close play
    Handler->>Repo: save(play)
    Handler-->>ApiAction: void
    ApiAction-->>Client: 200 OK
```

---

## 4. API Specification

| Method | Path                          | Auth     | Description     |
|--------|-------------------------------|----------|-----------------|
| PATCH  | `/v1/plays/sessions/{id}`     | Required | Close session   |

### Request

```json
{
    "started_at": "2026-02-22T19:00:00+00:00",
    "finished_at": "2026-02-22T22:30:00+00:00"
}
```

All fields optional. If `finished_at` not provided, use current server time.

### Response (200)

```json
{
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "status": "Closed"
    }
}
```

### Errors

- 401 Unauthorized -- missing or invalid token
- 403 Forbidden -- play belongs to another user
- 404 Not Found -- play does not exist
- 409 Conflict -- play already closed

---

## 5. Directory Structure

```
src/Application/Handlers/Plays/ClosePlay/
    Command.php
    Handler.php
    Result.php

config/common/openapi/
    plays.php       # Add close play endpoint
```

---

## 6. Implementation Considerations

### Edge Cases

- Play already closed: return 409 Conflict
- Play belongs to different user: return 403 Forbidden
- Missing finished_at: default to current server time
- started_at update: allow correcting the start time
- finished_at before started_at: validation error

### Security

- Must validate that authenticated user owns the play

---

## 7. Testing Strategy

### Functional Tests

- Handler closes open play successfully
- Handler rejects already closed play
- Handler rejects play owned by different user
- Handler uses default finished_at when not provided

### Integration Tests

- Full persistence: open play, then close it

### Acceptance Tests (Web)

- PATCH /v1/plays/sessions/{id} with valid token returns 200
- PATCH with wrong user returns 403
- PATCH already closed play returns 409
- PATCH without token returns 401

---

## 8. Acceptance Criteria

- [ ] ClosePlay Command + Handler + Result
- [ ] Play entity has `close()` method that validates state
- [ ] Ownership validation in handler
- [ ] OpenAPI config for PATCH `/v1/plays/sessions/{id}`
- [ ] Functional tests pass for all scenarios
- [ ] Integration test for full open->close play cycle
- [ ] Web acceptance tests pass
- [ ] `composer scan:all` passes

---

## Next Steps

Create implementation plan (master-checklist.md + stage files).
