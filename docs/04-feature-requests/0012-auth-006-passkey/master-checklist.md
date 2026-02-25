# Master Checklist: AUTH-006 Passkey (WebAuthn)

> Task: bgl-b88
> Created: 2026-02-25
> Depends on: AUTH-004, DB-MIGRATIONS, CORE-008

## Context

Passkey (FIDO2/WebAuthn) -- alternative passwordless authentication. After passkey verification, server issues the same JWT + refresh token pair. Current Authenticator mixes credential verification and token issuance -- extract TokenIssuer.

## Overview

**Overall Progress:** 0 of 5 stages completed
**Current Stage:** Stage 0

---

## Stage 0: TokenIssuer refactoring + ADR

### 0.1 ADR-014

- [ ] Create `docs/03-decisions/014-passkey-authentication.md`

### 0.2 TokenConfig (rename TokenTtlConfig)

- [ ] Rename `TokenTtlConfig` -> `TokenConfig` everywhere

### 0.3 TokenIssuer

- [ ] Create `src/Core/Auth/TokenIssuer.php`:
  ```php
  interface TokenIssuer
  {
      public function issue(string $userId): TokenPair;
  }
  ```
- [ ] Create `src/Infrastructure/Auth/JwtTokenIssuer.php`:
  - Extracted from `JwtAuthenticator.issueTokenPair()` (lines 147-169)
  - Depends on: Tokenizer, Users, TokenConfig
- [ ] Modify `src/Infrastructure/Auth/JwtAuthenticator.php`:
  - Inject TokenIssuer
  - Replace private `issueTokenPair()` -> `$this->tokenIssuer->issue($userId)`
- [ ] Modify `config/common/security.php` -- register via explicit factory:
  ```php
  TokenIssuer::class => static fn(Tokenizer $t, Users $u, TokenConfig $c): TokenIssuer => new JwtTokenIssuer($t, $u, $c),
  ```
- [ ] Verify: `composer lp:run && composer ps:run && composer test:unit`

---

## Stage 1: Domain Layer

### 1.1 Passkey Entity

- [ ] Create `src/Domain/Profile/Entities/Passkey.php`:
  - Pattern: follow User entity (final class, constructor with public id, static factory)
  - Fields: Uuid $id, Uuid $userId, string $credentialId, string $credentialData, int $counter, DateTimeImmutable $createdAt, ?string $label
  - Static factory: `Passkey::create(...)`
  - Mutable method: `updateCounter(int $counter): void`
  - Getters for all fields

### 1.2 PasskeyChallenge Entity

- [ ] Create `src/Domain/Profile/Entities/PasskeyChallenge.php`:
  - Pattern: follow User entity (final class, static factories, getters)
  - Fields: Uuid $id, string $challenge, DateTimeImmutable $expiresAt, ?Uuid $userId
  - Static factories: `forRegistration(...)`, `forLogin(...)`
  - Method: `isExpired(DateTimeImmutable $now): bool`

### 1.3 Passkeys Repository

- [ ] Create `src/Domain/Profile/Entities/Passkeys.php`:
  ```php
  /** @extends Repository<Passkey> */
  interface Passkeys extends Repository, Searchable
  {
      public function findByCredentialId(string $credentialId): ?Passkey;
      /** @return list<Passkey> */
      public function findAllByUserId(string $userId): array;
  }
  ```

### 1.4 PasskeyChallenges Repository

- [ ] Create `src/Domain/Profile/Entities/PasskeyChallenges.php`:
  ```php
  /** @extends Repository<PasskeyChallenge> */
  interface PasskeyChallenges extends Repository, Searchable
  {
      public function findByChallenge(string $challenge): ?PasskeyChallenge;
  }
  ```

### 1.5 Unit Tests

- [ ] `tests/Unit/Domain/Profile/Entities/PasskeyCest.php` -- create, updateCounter, getters
- [ ] `tests/Unit/Domain/Profile/Entities/PasskeyChallengeCest.php` -- create, isExpired, factories
- [ ] Verify: `composer lp:run && composer ps:run && composer test:unit`

---

## Stage 2: Infrastructure -- Persistence + Migration

### 2.1 Doctrine Mappings

- [ ] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Profile/PasskeyMapping.php`:
  - Table: `profile_passkey`
  - Fields: id (uuid_vo), user_id (uuid_vo), credential_id (string, unique), credential_data (text), counter (integer), created_at (datetime_immutable), label (string, nullable)
- [ ] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Profile/PasskeyChallengeMapping.php`:
  - Table: `profile_passkey_challenge`
  - Fields: id (uuid_vo), challenge (string, unique), expires_at (datetime_immutable), user_id (uuid_vo, nullable)

### 2.2 Register Mappings

- [ ] Modify `config/common/doctrine.php` -- add PasskeyMapping, PasskeyChallengeMapping to PhpMappingDriver

### 2.3 Doctrine Repositories

- [ ] Create `src/Infrastructure/Persistence/Doctrine/Passkeys.php`:
  - Extends DoctrineRepository (like Doctrine\Users), implements Domain\Profile\Entities\Passkeys
  - getType() -> Passkey::class, getAlias() -> 'p', getKeys() -> ['id']
  - findByCredentialId() via DQL
  - findAllByUserId() via DQL
- [ ] Create `src/Infrastructure/Persistence/Doctrine/PasskeyChallenges.php`:
  - Extends DoctrineRepository (like Doctrine\Users), implements Domain\Profile\Entities\PasskeyChallenges
  - getType() -> PasskeyChallenge::class, getAlias() -> 'pc', getKeys() -> ['id']
  - findByChallenge() via DQL

### 2.4 InMemory Repositories

- [ ] Create `src/Infrastructure/Persistence/InMemory/Passkeys.php`
- [ ] Create `src/Infrastructure/Persistence/InMemory/PasskeyChallenges.php`

### 2.5 DI Config

- [ ] Modify `config/common/persistence.php`:
  ```php
  Passkeys::class => static fn(EntityManagerInterface $em): Passkeys => new DoctrinePasskeys($em),
  PasskeyChallenges::class => static fn(EntityManagerInterface $em): PasskeyChallenges => new DoctrinePasskeyChallenges($em),
  ```

### 2.6 Migration

- [ ] Run `make migrate-gen` (auto-generates from mappings)
- [ ] Run `make migrate && make validate-schema`
- [ ] Verify: `composer lp:run && composer ps:run`

---

## Stage 3: Application Layer -- PasskeyVerifier + Handlers

### 3.1 Install Package

- [ ] `docker compose run --rm api-php-cli composer require web-auth/webauthn-lib`

### 3.2 PasskeyVerifier (Core interface)

- [ ] Create `src/Core/Auth/PasskeyVerifier.php`:
  ```php
  interface PasskeyVerifier
  {
      public function registerOptions(string $challenge, string $userId, string $userName): string;
      public function register(string $response, string $challenge): CredentialResult;
      public function loginOptions(string $challenge): string;
      public function login(string $response, string $challenge, string $credentialData): int;
  }
  ```
- [ ] Create `src/Core/Auth/CredentialResult.php`:
  ```php
  final readonly class CredentialResult
  {
      public function __construct(
          public string $credentialId,
          public string $credentialData,
      ) {}
  }
  ```

### 3.3 Infrastructure Adapter

- [ ] Create `src/Infrastructure/Auth/WebAuthnPasskeyVerifier.php`:
  - Implements PasskeyVerifier using `web-auth/webauthn-lib`
  - Config: rpId, rpName, origin from env vars

### 3.4 API Endpoints

| Endpoint | Method | Auth | Handler |
|---|---|---|---|
| `/v1/auth/passkey/register` | POST | Required | RegisterPasskeyOptions |
| `/v1/auth/passkey/register/verify` | POST | Required | RegisterPasskeyVerify |
| `/v1/auth/passkey/sign-in` | POST | Public | PasskeySignInOptions |
| `/v1/auth/passkey/sign-in/verify` | POST | Public | PasskeySignInVerify |

### 3.5 Handlers

**RegisterPasskeyOptions** -- generate challenge for registration:
- [ ] `src/Application/Handlers/Profile/RegisterPasskeyOptions/Command.php` -- userId (from auth)
- [ ] `src/Application/Handlers/Profile/RegisterPasskeyOptions/Handler.php`:
  1. Load user from Users repo
  2. Generate challenge via UuidGenerator or random_bytes
  3. Save PasskeyChallenge.forRegistration() to DB
  4. Call PasskeyVerifier.registerOptions(challenge, userId, userName)
  5. Return JSON options
- [ ] `src/Application/Handlers/Profile/RegisterPasskeyOptions/Result.php`

**RegisterPasskeyVerify** -- verify and save passkey:
- [ ] `src/Application/Handlers/Profile/RegisterPasskeyVerify/Command.php` -- userId (from auth), response (JSON string), label?
- [ ] `src/Application/Handlers/Profile/RegisterPasskeyVerify/Handler.php`:
  1. Find challenge by userId from PasskeyChallenges
  2. Check not expired
  3. Call PasskeyVerifier.register(response, challenge)
  4. Create Passkey entity, save to Passkeys repo
  5. Remove challenge

**PasskeySignInOptions** -- generate challenge for login:
- [ ] `src/Application/Handlers/Profile/PasskeySignInOptions/Command.php` -- empty
- [ ] `src/Application/Handlers/Profile/PasskeySignInOptions/Handler.php`:
  1. Generate challenge
  2. Save PasskeyChallenge.forLogin()
  3. Call PasskeyVerifier.loginOptions(challenge)
  4. Return JSON options
- [ ] `src/Application/Handlers/Profile/PasskeySignInOptions/Result.php`

**PasskeySignInVerify** -- verify passkey and issue tokens:
- [ ] `src/Application/Handlers/Profile/PasskeySignInVerify/Command.php` -- response (JSON string)
- [ ] `src/Application/Handlers/Profile/PasskeySignInVerify/Handler.php`:
  1. Extract credentialId from response
  2. Find Passkey by credentialId
  3. Find challenge from PasskeyChallenges
  4. Call PasskeyVerifier.login(response, challenge, credentialData) -> new counter
  5. Update passkey counter
  6. Call TokenIssuer.issue(userId)
  7. Return Result with TokenPair
- [ ] `src/Application/Handlers/Profile/PasskeySignInVerify/Result.php` -- accessToken, refreshToken, expiresIn

### 3.6 OpenAPI Config

- [ ] Modify `config/common/openapi/auth.php` -- add 4 endpoints with x-message, x-interceptors, x-auth

### 3.7 DI Config

- [ ] Modify `config/common/security.php` -- register PasskeyVerifier via explicit factory

### 3.8 Tests

- [ ] `tests/Functional/Api/Auth/PasskeyRegisterCest.php` -- register options + verify flow
- [ ] `tests/Functional/Api/Auth/PasskeySignInCest.php` -- sign-in options + verify flow
- [ ] Tests use real DB (fixtures), real handlers via MessageBus. WebAuthn mocked at adapter level in test DI config
- [ ] Verify: `composer lp:run && composer ps:run && composer test:func`

---

## Stage 4: Final Validation

- [ ] `composer scan:all` (MANDATORY)
- [ ] `composer dt:run` (deptrac)
- [ ] `composer test:all` (full test suite)

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
| 0 | Not Started | - | TokenIssuer + ADR |
| 1 | Not Started | - | Domain layer |
| 2 | Not Started | - | Persistence + migration |
| 3 | Not Started | - | Handlers + WebAuthn |
| 4 | Not Started | - | Final validation |
