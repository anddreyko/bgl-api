# Documentation: View Session (PLAYS-003)

**Completed:** 2026-03-01

---

## Summary

Implemented `GET /v1/plays/sessions/{id}` endpoint for viewing play session details with visibility-based access control.

## API

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/v1/plays/sessions/{id}` | Optional (Bearer) | View session details |

### Access Control Matrix

| Visibility | No Token | Token (owner) | Token (other) |
|------------|----------|---------------|---------------|
| private | 404 | 200 | 404 |
| link | 200 | 200 | 200 |
| participants | 401 | 200 | 200 if player, else 404 |
| authenticated | 401 | 200 | 200 |
| public | 200 | 200 | 200 |

Draft sessions are visible to owner only, regardless of visibility setting.

### Response

```json
{
    "code": 0,
    "data": {
        "id": "uuid",
        "name": "string|null",
        "status": "draft|published",
        "visibility": "private|link|participants|authenticated|public",
        "started_at": "ISO 8601|null",
        "finished_at": "ISO 8601|null",
        "game": { "id": "uuid", "name": "string" } | null,
        "players": [
            { "id": "uuid", "mate_id": "uuid", "score": int|null, "is_winner": bool, "color": "string|null" }
        ]
    }
}
```

## Architecture

### OptionalAuthInterceptor

New interceptor at `src/Presentation/Api/Interceptors/OptionalAuthInterceptor.php`. Extracts `userId` from Bearer token if present; sets `null` for anonymous access (no exception thrown).

### GetPlay Handler

- **Handler:** `src/Application/Handlers/Plays/GetPlay/Handler.php`
- **Query:** `src/Application/Handlers/Plays/GetPlay/Query.php`
- **Result:** `src/Application/Handlers/Plays/GetPlay/Result.php`

Visibility check uses `match` expression over `Visibility` enum. Participants access verified by iterating players, resolving their Mates, and checking `userId` match.

## Files Changed

| File | Action |
|------|--------|
| `src/Presentation/Api/Interceptors/OptionalAuthInterceptor.php` | Created |
| `src/Application/Handlers/Plays/GetPlay/Query.php` | Created |
| `src/Application/Handlers/Plays/GetPlay/Handler.php` | Created |
| `src/Application/Handlers/Plays/GetPlay/Result.php` | Created |
| `config/common/bus.php` | Modified |
| `config/_serialise-mapping.php` | Modified |
| `config/common/openapi/plays.php` | Modified |
| `tests/Unit/Presentation/Api/Interceptors/OptionalAuthInterceptorCest.php` | Created |
| `tests/Functional/Plays/GetPlayCest.php` | Created |
| `tests/Web/PlaySessionCest.php` | Modified |

## Test Coverage

### Unit (4 tests)

- testWithValidTokenSetsUserId
- testWithoutTokenSetsNull
- testWithInvalidTokenSetsNull
- testWithNonBearerHeaderSetsNull

### Functional (13 tests)

- testOwnerViewsPrivateSession
- testNonOwnerDeniedPrivateSession
- testAnonymousViewsPublicSession
- testAnonymousViewsLinkSession
- testAnonymousDeniedAuthenticatedSession
- testAuthenticatedViewsAuthenticatedSession
- testParticipantsVisibilityPlayerAccess
- testAnonymousDeniedParticipantsSession
- testParticipantsVisibilityNonPlayerDenied
- testNonExistentSessionThrowsNotFound
- testDraftSessionOwnerOnly
- testAnonymousDeniedDraftSession
- testResultContainsPlayerData

### Web Acceptance (2 tests)

- testGetSessionReturns200ForOwner
- testGetSessionReturns404ForNonExistent

## Known Limitations

- Cross-context dependency: Handler directly queries `Mates` repository from another Bounded Context for Participants visibility check. Architectural debt tracked separately.
- N+1 in participants check: `mates->find()` per player in loop.
