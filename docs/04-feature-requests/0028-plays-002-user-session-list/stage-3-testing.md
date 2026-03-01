# Stage 3: Testing + Validation (PLAYS-002)

## Goal

Full test coverage for ListPlays and final validation.

## Tasks

### 1. Functional Tests

**File:** `tests/Functional/Plays/ListPlaysCest.php`

Setup: create user, mates, games, multiple Play sessions via CreatePlay handler.

Tests:
- `testListPlaysReturnsUserSessions`: create 3 sessions, verify all returned
- `testListPlaysWithPagination`: create 5 sessions, page=1 size=2, verify 2 items + total=5
- `testListPlaysFilterByGameId`: create sessions with different games, filter by one
- `testListPlaysFilterByDateRange`: create sessions at different dates, filter from/to
- `testListPlaysEmptyResult`: user with no sessions, verify empty items + total=0
- `testListPlaysDoesNotShowOtherUserSessions`: create sessions for 2 users, verify isolation
- `testListPlaysSortedByStartedAtDesc`: verify ordering

### 2. Web Acceptance Tests

**File:** `tests/Web/PlaySessionCest.php` (add methods)

- `testListSessionsReturns200`: register, create session, GET /v1/plays/sessions, verify 200 + JSON structure
- `testListSessionsWithoutTokenReturns401`: GET without Bearer, verify 401

### 3. Full Validation

```bash
make scan           # MANDATORY
composer test:func  # Functional tests
composer test:web   # Web acceptance tests
```
