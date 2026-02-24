# Master Checklist: AUTH-004 Auth Interceptors + AUTH-012 Authenticator Refactor

> Tasks: bgl-z0v (original, done), bgl-4xz (AUTH-012)
> Created: 2026-02-23

## Overview

**Overall Progress:** 6 of 6 stages completed
**Current Stage:** All stages complete

---

## Stage 1: AuthInterceptor (~25min) -- DONE

**Dependencies:** AUTH-002 complete

- [x] Create `src/Presentation/Api/Interceptors/AuthInterceptor.php`
- [x] Register AuthInterceptor in DI
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: SchemaMapper x-source Extension (~20min) -- DONE

**Dependencies:** Stage 1

- [x] OpenApiSchemaMapper: `x-source: attribute:*` support
- [x] Unit tests for AuthInterceptor
- [x] Verify: `composer test:unit`

---

## Stage 3: Final Validation (~10min) -- DONE

- [x] `composer scan:all` passes
- [x] `composer dt:run` passes

---

## Stage 4: Authenticator Interface + Value Objects (~20min) [P] -- DONE

**Dependencies:** None (AUTH-012 starts here)

- [x] Create `src/Core/Auth/Authenticator.php` interface:
  - `login(string $email, string $password): TokenPair`
  - `refresh(string $refreshToken): TokenPair`
  - `revoke(string $userId): void`
  - `verify(string $accessToken): AuthPayload`
- [x] Create `src/Core/Auth/TokenPair.php` readonly VO:
  - `string $accessToken`, `string $refreshToken`, `int $expiresIn`
- [x] Create `src/Core/Auth/AuthPayload.php` readonly VO:
  - `string $userId`
- [x] Delete old `src/Core/Auth/Authentificator.php` (replaced by Authenticator)
- [x] Delete `src/Core/Auth/Identity.php`
- [x] Delete `src/Core/Auth/Identities.php`
- [x] Delete `src/Core/Auth/GrantType.php`
- [x] Delete `src/Core/Auth/GrantNotSupportedException.php`
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 5: JwtAuthenticator + Simplified Handlers (~40min) -- DONE

**Dependencies:** Stage 4

- [x] Create `src/Infrastructure/Auth/JwtAuthenticator.php` implementing Authenticator:
  - Constructor: `TokenGenerator`, `Users`, `PasswordHasher`, `TokenTtlConfig`
  - `login()`: find user by email, verify password, check status Active, issue token pair with tokenVersion
  - `refresh()`: verify token, check type=refresh, find user, check status, check tokenVersion, issue new pair
  - `revoke()`: find user, incrementTokenVersion
  - `verify()`: verify token, check type=access, find user, check tokenVersion, return AuthPayload
- [x] Simplify `src/Application/Handlers/Auth/LoginByCredentials/Handler.php`:
  - Replace direct TokenGenerator/Users/PasswordHasher with Authenticator
  - `$tokenPair = $this->authenticator->login($command->email, $command->password)`
- [x] Simplify `src/Application/Handlers/Auth/RefreshToken/Handler.php`:
  - Replace direct TokenGenerator/Users with Authenticator
  - `$tokenPair = $this->authenticator->refresh($command->refreshToken)`
- [x] Simplify `src/Application/Handlers/Auth/SignOut/Handler.php`:
  - Replace direct Users with Authenticator
  - `$this->authenticator->revoke($command->userId)`
- [x] Update `src/Presentation/Api/Interceptors/AuthInterceptor.php`:
  - Replace direct TokenGenerator/Users with Authenticator
  - `$payload = $this->authenticator->verify($token)`
- [x] Update DI config: bind `Authenticator` -> `JwtAuthenticator`
- [x] Update handler unit tests for new signatures
- [x] Write unit tests for JwtAuthenticator (login/refresh/revoke/verify -- success + error cases)
- [x] Add `@see` cross-references
- [x] Verify: `composer lp:run && composer ps:run && composer test:unit`
- [x] Run `composer test:web` -- E2E must pass

---

## Stage 6: Cleanup + ADR Update (~20min) -- DONE

**Dependencies:** Stage 5

- [x] Delete `src/Infrastructure/Authentification/` directory (entire)
- [x] Delete `tests/Integration/Infrastructure/Authentification/` directory
- [x] Remove `league/oauth2-server`: `docker compose run --rm api-php-cli composer remove league/oauth2-server`
- [x] Update `docs/03-decisions/009-league-php-preference.md`:
  - Change `league/oauth2-server` status from "Planned" to "Deferred -- JWT-based auth for MVP, OAuth2 later via Authenticator interface swap"
- [x] Remove old DI bindings for deleted classes
- [x] Run `composer scan:all` (MANDATORY) -- 0 errors
- [x] Run `composer dt:run` (deptrac) -- 0 violations
- [x] Run `composer test:all` (full test suite) -- 191 unit + 52 func + 11 intg + 6 E2E pass

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Core/Auth/Authenticator.php` | CREATE | 4 |
| `src/Core/Auth/TokenPair.php` | CREATE | 4 |
| `src/Core/Auth/AuthPayload.php` | CREATE | 4 |
| `src/Core/Auth/Authentificator.php` | DELETE | 4 |
| `src/Core/Auth/Identity.php` | DELETE | 4 |
| `src/Core/Auth/Identities.php` | DELETE | 4 |
| `src/Core/Auth/GrantType.php` | DELETE | 4 |
| `src/Core/Auth/GrantNotSupportedException.php` | DELETE | 4 |
| `src/Infrastructure/Auth/JwtAuthenticator.php` | CREATE | 5 |
| `src/Application/Handlers/Auth/LoginByCredentials/Handler.php` | MODIFY | 5 |
| `src/Application/Handlers/Auth/RefreshToken/Handler.php` | MODIFY | 5 |
| `src/Application/Handlers/Auth/SignOut/Handler.php` | MODIFY | 5 |
| `src/Presentation/Api/Interceptors/AuthInterceptor.php` | MODIFY | 5 |
| `config/common/security.php` | MODIFY | 5 |
| `config/common/interceptors.php` | MODIFY | 5 |
| `tests/Unit/Infrastructure/Auth/JwtAuthenticatorCest.php` | CREATE | 5 |
| `src/Infrastructure/Authentification/` | DELETE | 6 |
| `tests/Integration/Infrastructure/Authentification/` | DELETE | 6 |
| `docs/03-decisions/009-league-php-preference.md` | MODIFY | 6 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-02-23 | AuthInterceptor created |
| 2 | Done | 2026-02-23 | x-source support added |
| 3 | Done | 2026-02-23 | All checks pass |
| 4 | Done | 2026-02-23 | Authenticator interface + VOs created, old auth code deleted |
| 5 | Done | 2026-02-23 | JwtAuthenticator implemented, handlers simplified, tests rewritten |
| 6 | Done | 2026-02-23 | OAuth2 code removed, league/oauth2-server uninstalled, scan:all clean |
