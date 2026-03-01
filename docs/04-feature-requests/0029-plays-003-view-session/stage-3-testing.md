# Stage 3: Testing + Validation (PLAYS-003)

## Goal

Full test coverage for GetPlay with all visibility scenarios.

## Tasks

### 1. Functional Tests

**File:** `tests/Functional/Plays/GetPlayCest.php`

Setup: create users, mates, play sessions with different visibility.

Tests:
- `testOwnerViewsPrivateSession`: owner with private visibility, returns Result
- `testNonOwnerDeniedPrivateSession`: other user, throws NotFoundException
- `testAnonymousViewsPublicSession`: userId=null, public visibility, returns Result
- `testAnonymousViewsLinkSession`: userId=null, link visibility, returns Result
- `testAnonymousDeniedRegisteredSession`: userId=null, registered visibility, throws AuthenticationException
- `testAuthenticatedViewsRegisteredSession`: other user, registered visibility, returns Result
- `testFriendsVisibilityPlayerAccess`: user whose mate is player, returns Result
- `testFriendsVisibilityNonPlayerDenied`: user whose mate is NOT player, throws NotFoundException
- `testNonExistentSessionThrowsNotFound`: random ID, throws NotFoundException
- `testDraftSessionOwnerOnly`: draft status, non-owner denied

### 2. Web Acceptance Tests

**File:** `tests/Web/PlaySessionCest.php` (add methods)

- `testGetSessionReturns200ForOwner`: register, create session, GET by ID, verify 200 + JSON
- `testGetSessionReturns404ForNonExistent`: GET with random UUID, verify 404

### 3. Full Validation

```bash
make scan           # MANDATORY
composer test:func  # Functional tests
composer test:web   # Web acceptance tests
composer test:unit  # Unit tests (OptionalAuthInterceptor)
```
