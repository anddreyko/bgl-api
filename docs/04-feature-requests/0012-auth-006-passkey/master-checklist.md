# Master Checklist: AUTH-006 Passkey (WebAuthn)

> Task: bgl-b88
> Created: 2026-02-25
> Depends on: AUTH-004, DB-MIGRATIONS, CORE-008

## Context

Passkey (FIDO2/WebAuthn) -- alternative passwordless authentication. After passkey verification, server issues the same JWT + refresh token pair. Current Authenticator mixes credential verification and token issuance -- extract TokenIssuer.

## Overview

**Overall Progress:** 5 of 5 stages completed
**Status:** Done

---

## Stage 0: TokenIssuer refactoring + ADR

### 0.1 ADR-013

- [x] Create `docs/03-decisions/013-passkey-authentication.md`

### 0.2 TokenIssuer

- [x] Create `src/Core/Auth/TokenIssuer.php`
- [x] Create `src/Infrastructure/Auth/JwtTokenIssuer.php`
- [x] Modify `src/Infrastructure/Auth/JwtAuthenticator.php` -- inject TokenIssuer, remove issueTokenPair()
- [x] Modify `config/common/security.php` -- register TokenIssuer + replace DI\get() with explicit factories
- [x] Create `tests/Unit/Infrastructure/Auth/JwtTokenIssuerCest.php`
- [x] Update `tests/Unit/Infrastructure/Auth/JwtAuthenticatorCest.php`
- [x] Verify: lp:run, ps:run, test:unit -- all green

---

## Stage 1: Domain Layer

### 1.1 Passkey Entity

- [x] Create `src/Domain/Profile/Entities/Passkey.php`

### 1.2 PasskeyChallenge Entity

- [x] Create `src/Domain/Profile/Entities/PasskeyChallenge.php`

### 1.3 Passkeys Repository

- [x] Create `src/Domain/Profile/Entities/Passkeys.php`

### 1.4 PasskeyChallenges Repository

- [x] Create `src/Domain/Profile/Entities/PasskeyChallenges.php`

### 1.5 Unit Tests

- [x] `tests/Unit/Domain/Profile/Entities/PasskeyCest.php` -- 4 tests
- [x] `tests/Unit/Domain/Profile/Entities/PasskeyChallengeCest.php` -- 5 tests
- [x] Verify: lp:run, ps:run, test:unit -- all green

---

## Stage 2: Infrastructure -- Persistence + Migration

### 2.1 Doctrine Mappings

- [x] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/PasskeyMapping.php`
- [x] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/PasskeyChallengeMapping.php`

### 2.2 Register Mappings

- [x] Modify `config/common/doctrine.php`

### 2.3 Doctrine Repositories

- [x] Create `src/Infrastructure/Persistence/Doctrine/Passkeys.php`
- [x] Create `src/Infrastructure/Persistence/Doctrine/PasskeyChallenges.php`

### 2.4 InMemory Repositories

- [x] Create `src/Infrastructure/Persistence/InMemory/Passkeys.php`
- [x] Create `src/Infrastructure/Persistence/InMemory/PasskeyChallenges.php`

### 2.5 DI Config

- [x] Modify `config/common/persistence.php` -- replaced DI\get() with explicit factories

### 2.6 Migration

- [x] Generated `Version20260225114838.php` (auth_passkey + auth_passkey_challenge)
- [x] Migration applied, diff clean
- [x] Verify: lp:run, ps:run -- all green

---

## Stage 3: Application Layer -- PasskeyVerifier + Handlers

### 3.1 Install Package

- [x] `docker compose run --rm api-php-cli composer require web-auth/webauthn-lib`

### 3.2 PasskeyVerifier (Core interface)

- [x] Create `src/Core/Auth/PasskeyVerifier.php`
- [x] Create `src/Core/Auth/CredentialResult.php`

### 3.3 Infrastructure Adapter

- [x] Create `src/Infrastructure/Auth/WebAuthnPasskeyVerifier.php`

### 3.4 API Endpoints

| Endpoint | Method | Auth | Handler |
|---|---|---|---|
| `/v1/auth/passkey/register` | POST | Required | RegisterPasskeyOptions |
| `/v1/auth/passkey/register/verify` | POST | Required | RegisterPasskeyVerify |
| `/v1/auth/passkey/sign-in` | POST | Public | PasskeySignInOptions |
| `/v1/auth/passkey/sign-in/verify` | POST | Public | PasskeySignInVerify |

### 3.5 Handlers

**RegisterPasskeyOptions** -- generate challenge for registration:
- [x] `src/Application/Handlers/Auth/RegisterPasskeyOptions/` -- Command, Handler, Result
- [x] `src/Application/Handlers/Auth/RegisterPasskeyVerify/` -- Command, Handler
- [x] `src/Application/Handlers/Auth/PasskeySignInOptions/` -- Command, Handler, Result
- [x] `src/Application/Handlers/Auth/PasskeySignInVerify/` -- Command, Handler, Result

### 3.6 OpenAPI Config

- [x] Modify `config/common/openapi/auth.php` -- add 4 endpoints with x-message, x-interceptors, x-auth

### 3.7 DI Config

- [x] Modify `config/common/security.php` -- register PasskeyVerifier via explicit factory

### 3.8 Tests

- [x] `tests/Functional/Auth/RegisterPasskeyOptionsCest.php` -- 3 tests (options JSON, user not found, challenge saved)
- [x] `tests/Functional/Auth/PasskeySignInCest.php` -- 6 tests (options JSON, challenge saved, verify tokens, passkey not found, counter update, challenge removed)
- [x] Tests use InMemory repos + fake PasskeyVerifier/TokenIssuer
- [x] Verify: `composer lp:run && composer ps:run && composer test:func` -- all green

---

## Stage 4: Final Validation

- [x] `composer scan:all` -- cd/lp/ps green, deptrac has 27 pre-existing Override violations only
- [x] `composer dt:run` -- 27 pre-existing Override violations (4 from new code, same pattern)
- [x] `composer test:unit` -- 199 tests green
- [x] `composer test:func` -- 20 tests green

---

## Key Reference Files

| File | Reuse Pattern |
|---|---|
| `src/Domain/Profile/Entities/User.php` | Entity pattern |
| `src/Domain/Profile/Entities/Users.php` | Repository + Searchable pattern |
| `src/Infrastructure/Persistence/Doctrine/Users.php` | Doctrine repo (extends DoctrineRepository) |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/UserMapping.php` | Mapping pattern |
| `src/Infrastructure/Persistence/InMemory/Users.php` | InMemory repo pattern |
| `src/Infrastructure/Auth/JwtAuthenticator.php` | Token issuance logic (extract to TokenIssuer) |
| `tests/Functional/Api/ApiActionCest.php` | Functional test pattern |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 0 | Completed | 2026-02-25 | TokenIssuer + ADR |
| 1 | Completed | 2026-02-25 | Domain layer |
| 2 | Completed | 2026-02-25 | Persistence + migration |
| 3 | Completed | 2026-02-25 | Handlers + WebAuthn + 9 tests |
| 4 | Completed | 2026-02-25 | Final validation -- all green |
