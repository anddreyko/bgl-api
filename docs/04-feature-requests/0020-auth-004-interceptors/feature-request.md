# Feature Request: AUTH-004 Authentication and Authorization

**Tasks:** bgl-z0v (original), bgl-4xz (AUTH-012 refactor)
**Date:** 2026-02-23
**Status:** Partially implemented
**Priority:** P1

---

## 1. Feature Overview

### Description

Authentication and authorization layer for the API. Includes:
- AuthInterceptor for protected endpoints (DONE)
- Authenticator interface as the core contract for auth operations
- JwtAuthenticator as the current implementation
- Future-proof design for OAuth2 migration (additive, not rewrite)

### Current State (implemented)

- AuthInterceptor: extracts Bearer token, validates JWT, loads user, attaches userId to request attributes
- LoginByCredentials handler: verifies password, issues JWT token pair
- RefreshToken handler: verifies refresh token, issues new pair
- SignOut handler: increments tokenVersion to revoke all tokens
- TokenGenerator interface + JwtTokenGenerator implementation
- TokenTtlConfig with env-based TTL

### Problem

Auth logic (token issuance, verification, revocation) is spread across 4 files (LoginByCredentials, RefreshToken, SignOut handlers + AuthInterceptor), each directly using TokenGenerator + Users. When OAuth2 is added later, all 4 files would need rewriting. Dead OAuth2 code (LeagueAuthServer, OpenAuth/) exists but is unused and incompatible.

### Solution (AUTH-012)

Extract token lifecycle into `Authenticator` interface (Core/Auth). Current JWT logic becomes `JwtAuthenticator` (Infrastructure). Handlers delegate to Authenticator. When OAuth2 is needed, create `OAuth2Authenticator` -- swap via DI, handlers unchanged.

---

## 2. Technical Architecture

### Authenticator Interface (Core/Auth)

```php
interface Authenticator {
    public function login(string $email, string $password): TokenPair;
    public function refresh(string $refreshToken): TokenPair;
    public function revoke(string $userId): void;
    public function verify(string $accessToken): AuthPayload;
}
```

### Value Objects (Core/Auth)

- `TokenPair` -- accessToken, refreshToken, expiresIn
- `AuthPayload` -- userId (extracted from verified token)

### JwtAuthenticator (Infrastructure/Auth)

Encapsulates current logic from handlers:
- `login()` -- find user by email, verify password (PasswordHasher), check status, issue JWT pair (TokenGenerator)
- `refresh()` -- verify refresh token, check type/userId/tokenVersion, issue new pair
- `revoke()` -- find user, incrementTokenVersion
- `verify()` -- verify access token, check type/userId/tokenVersion, return AuthPayload

### Future OAuth2 Migration

```php
// DI config change only:
Authenticator::class => DI\get(OAuth2Authenticator::class),
```

Handlers and AuthInterceptor unchanged -- they depend on Authenticator interface.

---

## 3. Dead Code to Remove

```
src/Infrastructure/Authentification/OpenAuth/LeagueAuthServer.php
src/Infrastructure/Authentification/OpenAuth/Users.php
src/Infrastructure/Authentification/OpenAuth/UserId.php
tests/Integration/Infrastructure/Authentification/LeagueAuthServerCest.php
src/Core/Auth/Authentificator.php          (replaced by Authenticator)
src/Core/Auth/Identity.php                 (unused abstraction)
src/Core/Auth/Identities.php               (unused abstraction)
src/Core/Auth/GrantType.php                (unused)
src/Core/Auth/GrantNotSupportedException.php (unused)
```

Package to remove: `league/oauth2-server`

---

## 4. Testing Strategy

### Unit Tests

- JwtAuthenticator: login success, invalid credentials, email not confirmed, token version mismatch
- JwtAuthenticator: refresh success, invalid token, expired token, revoked token
- JwtAuthenticator: verify success, invalid token, wrong type
- JwtAuthenticator: revoke increments tokenVersion
- TokenPair, AuthPayload: value objects

### Existing Tests

- Handler unit tests need updating (simplified handler logic)
- E2E tests (AuthFlowCest) must stay green -- behavior unchanged

---

## 5. Acceptance Criteria

- [x] AuthInterceptor implemented (original AUTH-004)
- [ ] Authenticator interface in Core/Auth with login/refresh/revoke/verify
- [ ] TokenPair and AuthPayload value objects in Core/Auth
- [ ] JwtAuthenticator in Infrastructure/Auth
- [ ] Handlers simplified to delegate to Authenticator
- [ ] AuthInterceptor uses Authenticator::verify()
- [ ] Dead OAuth2 code removed
- [ ] league/oauth2-server package removed
- [ ] ADR-009 updated
- [ ] `composer scan:all` passes
- [ ] `composer test:web` passes
- [ ] `composer dt:run` passes
