# Master Checklist: AUTH-001 Registration + Email Confirmation

> Task: bgl-zck
> Created: 2026-02-23

## Overview

**Overall Progress:** 5 of 5 stages completed

---

## Stage 1: Domain Layer (~30min)

**Dependencies:** None

- [x] Modify `src/Domain/Auth/Entities/User.php`:
  - Remove `readonly` from class
  - Add `private string $passwordHash` property
  - Add static `register(Uuid $id, Email $email, string $passwordHash, \DateTimeImmutable $createdAt): self` factory
  - Add `confirm(): void` method (sets status to Active)
  - Add `getPasswordHash(): string` getter
- [x] Add `findByEmail(string $email): ?User` to `src/Domain/Auth/Entities/Users.php` interface
- [x] Create `src/Domain/Auth/Entities/EmailConfirmationToken.php`:
  - Properties: Uuid $id, Uuid $userId, string $token, \DateTimeImmutable $expiresAt
  - Static factory: `create(Uuid $userId, string $token, \DateTimeImmutable $expiresAt): self`
  - Method: `isExpired(\DateTimeImmutable $now): bool`
- [x] Create `src/Domain/Auth/Entities/EmailConfirmationTokens.php` repository interface:
  - `add(EmailConfirmationToken $token): void`
  - `findByToken(string $token): ?EmailConfirmationToken`
  - `remove(EmailConfirmationToken $token): void`
- [x] Create domain exceptions in `src/Domain/Auth/Exceptions/`:
  - `UserAlreadyExistsException extends \DomainException`
  - `InvalidConfirmationTokenException extends \DomainException`
  - `ExpiredConfirmationTokenException extends \DomainException`
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Infrastructure Layer (~30min)

**Dependencies:** Stage 1

- [x] Update `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/UserMapping.php`:
  - Add passwordHash field mapping (type: string)
  - Change createdAt type from `date_immutable` to `datetime_immutable`
- [x] Create `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/EmailConfirmationTokenMapping.php`
- [x] Update `src/Infrastructure/Persistence/Doctrine/Users.php`:
  - Add `findByEmail(string $email): ?User` using DQL
- [x] Create `src/Infrastructure/Persistence/Doctrine/EmailConfirmationTokens.php`
- [x] Register new mapping in `config/common/doctrine.php` PhpMappingDriver
- [x] Register new repositories in `config/common/persistence.php`
- [x] Generate migration: `make migrate-gen` (adds password_hash to auth_user + creates auth_email_confirmation_token table)
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 3: Application Layer (~25min)

**Dependencies:** Stage 2

- [x] Create `src/Application/Handlers/Auth/Register/Command.php`:
  - Properties: string $email, string $password
  - Implements `Message<string>`
- [x] Create `src/Application/Handlers/Auth/Register/Handler.php`:
  - Dependencies: Users, EmailConfirmationTokens, PasswordHasher, ClockInterface
  - Logic: check uniqueness -> hash password -> create User (Inactive) -> create token -> return message
- [x] Create `src/Application/Handlers/Auth/ConfirmEmail/Command.php`:
  - Properties: string $token
  - Implements `Message<string>`
- [x] Create `src/Application/Handlers/Auth/ConfirmEmail/Handler.php`:
  - Dependencies: Users, EmailConfirmationTokens, ClockInterface
  - Logic: find token -> check expiry -> find user -> confirm -> remove token -> return message
- [x] Register handlers in `config/common/bus.php`
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 4: OpenAPI Config + Tests (~30min)

**Dependencies:** Stage 3

- [x] Create `config/common/openapi/auth.php` with routes:
  - POST /v1/auth/sign-up -> Register\Command (email, password in body)
  - GET /v1/auth/confirm/{token} -> ConfirmEmail\Command (token from path)
- [x] Create unit tests for Register Handler
- [x] Create unit tests for ConfirmEmail Handler
- [x] Create unit test for EmailConfirmationToken entity
- [x] Verify: `composer test:unit`

---

## Stage 5: Final Validation (~15min)

**Dependencies:** Stage 4

- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer dt:run` (architecture check)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Domain/Auth/Entities/User.php` | MODIFY | 1 |
| `src/Domain/Auth/Entities/Users.php` | MODIFY | 1 |
| `src/Domain/Auth/Entities/EmailConfirmationToken.php` | CREATE | 1 |
| `src/Domain/Auth/Entities/EmailConfirmationTokens.php` | CREATE | 1 |
| `src/Domain/Auth/Exceptions/UserAlreadyExistsException.php` | CREATE | 1 |
| `src/Domain/Auth/Exceptions/InvalidConfirmationTokenException.php` | CREATE | 1 |
| `src/Domain/Auth/Exceptions/ExpiredConfirmationTokenException.php` | CREATE | 1 |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/UserMapping.php` | MODIFY | 2 |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/EmailConfirmationTokenMapping.php` | CREATE | 2 |
| `src/Infrastructure/Persistence/Doctrine/Users.php` | MODIFY | 2 |
| `src/Infrastructure/Persistence/Doctrine/EmailConfirmationTokens.php` | CREATE | 2 |
| `config/common/doctrine.php` | MODIFY | 2 |
| `config/common/persistence.php` | MODIFY | 2 |
| `src/Application/Handlers/Auth/Register/Command.php` | CREATE | 3 |
| `src/Application/Handlers/Auth/Register/Handler.php` | CREATE | 3 |
| `src/Application/Handlers/Auth/ConfirmEmail/Command.php` | CREATE | 3 |
| `src/Application/Handlers/Auth/ConfirmEmail/Handler.php` | CREATE | 3 |
| `config/common/bus.php` | MODIFY | 3 |
| `config/common/openapi/auth.php` | CREATE | 4 |
