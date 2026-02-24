# Documentation: AUTH-004 Auth Interceptors + AUTH-012 Authenticator Refactor

> Tasks: bgl-z0v, bgl-4xz
> Completed: 2026-02-23

## Summary

Implemented `Authenticator` interface in Core layer with `JwtAuthenticator` implementation in Infrastructure.
Consolidated all authentication logic (login, refresh, revoke, verify) from 4 separate handlers into a single
service. Simplified handlers to thin delegators. Removed dead OAuth2 code and `league/oauth2-server` dependency.

## Architecture

### Before

```
LoginHandler     -> Users + PasswordHasher + TokenGenerator + TokenTtlConfig (auth logic inline)
RefreshHandler   -> TokenGenerator + Users + TokenTtlConfig (auth logic inline)
SignOutHandler   -> Users (auth logic inline)
AuthInterceptor  -> TokenGenerator + Users (auth logic inline)
```

### After

```
Core:             Authenticator interface + TokenPair + AuthPayload VOs
Infrastructure:   JwtAuthenticator implements Authenticator (all auth logic centralized)
Application:      Handlers delegate to Authenticator (1 dependency each)
Presentation:     AuthInterceptor delegates to Authenticator (1 dependency)
```

## Key Design Decisions

1. **Single Authenticator interface** -- all auth operations behind one contract. Handlers depend only on this.
2. **TokenPair + AuthPayload VOs in Core** -- type-safe return values, no leaking infrastructure details.
3. **Token version checking** -- revoke increments user's tokenVersion, verify/refresh check it matches.
4. **OAuth2 deferred** -- `league/oauth2-server` removed for MVP. Future OAuth2 support via `OAuth2Authenticator`
   implementing the same `Authenticator` interface (zero handler changes needed).

## Files Changed

### Created

- `src/Core/Auth/Authenticator.php` -- interface with login/refresh/revoke/verify
- `src/Core/Auth/TokenPair.php` -- readonly VO for access+refresh token pair
- `src/Core/Auth/AuthPayload.php` -- readonly VO for verified auth payload
- `src/Infrastructure/Auth/JwtAuthenticator.php` -- JWT implementation of Authenticator
- `tests/Unit/Infrastructure/Auth/JwtAuthenticatorCest.php` -- 19 test cases

### Modified

- `src/Application/Handlers/Auth/LoginByCredentials/Handler.php` -- simplified to Authenticator
- `src/Application/Handlers/Auth/RefreshToken/Handler.php` -- simplified to Authenticator
- `src/Application/Handlers/Auth/SignOut/Handler.php` -- simplified to Authenticator
- `src/Presentation/Api/Interceptors/AuthInterceptor.php` -- simplified to Authenticator
- `config/common/security.php` -- added Authenticator DI binding
- `config/common/interceptors.php` -- updated to use Authenticator
- `docs/03-decisions/009-league-php-preference.md` -- OAuth2 status updated

### Deleted

- `src/Core/Auth/Authentificator.php` -- replaced by Authenticator
- `src/Core/Auth/Identity.php` -- replaced by AuthPayload
- `src/Core/Auth/Identities.php` -- unused
- `src/Core/Auth/GrantType.php` -- unused (OAuth2 deferred)
- `src/Core/Auth/GrantNotSupportedException.php` -- unused
- `src/Infrastructure/Authentification/` -- dead OAuth2 implementation (3 files)
- `tests/Integration/Infrastructure/Authentification/` -- dead OAuth2 tests
- `tests/Unit/Core/Auth/GrantTypeCest.php` -- tests for deleted code
- `tests/Unit/Core/Auth/IdentityCest.php` -- tests for deleted code

### Removed Dependencies

- `league/oauth2-server` (+ transitive: `league/event`, `lcobucci/clock`, `defuse/php-encryption`)

## Test Coverage

- JwtAuthenticatorCest: 19 tests (login 4 + refresh 7 + revoke 2 + verify 8)
- Handler tests: simplified to mock Authenticator (2 tests each)
- AuthInterceptorCest: 4 tests (header validation + Authenticator delegation)
- E2E: 6 tests pass (registration, login, sign-out, token refresh, user info, play session)

## Verification

```
composer scan:all -- 0 errors, 0 violations
  Psalm: No errors found
  Deptrac: 0 violations
  Unit: 191 tests, 308 assertions
  Functional: 52 tests, 71 assertions
  Integration: 11 tests, 38 assertions
  Web E2E: 6 tests, 37 assertions
```
