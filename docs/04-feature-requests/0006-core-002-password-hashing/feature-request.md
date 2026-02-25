# Feature Request: Password Hashing Contract and Component (CORE-002)

**Beads ID:** bgl-w52
**Date:** 2026-02-22
**Status:** In Progress
**Priority:** P1 (Foundation)

---

## 1. Overview

Port interface and implementations for password hashing. Follows Ports & Adapters pattern: contract in Core,
implementations in Infrastructure.

### Problem

Currently `User` entity has no password field. The auth flow (`LeagueAuthServer` -> `Users::findByCredentials`) passes
passwords as plain text. This is a security vulnerability and blocks AUTH-001 (Registration) and AUTH-002 (Login).

### Scope

- `PasswordHasher` interface in `src/Core/Security/`
- `BcryptPasswordHasher` implementation in `src/Infrastructure/Security/`
- DI config in `config/common/security.php`

### Out of Scope

- TokenGenerator -- separate task (CORE-008)
- User entity password field -- AUTH-001 task
- Modifying auth flow to use hasher -- AUTH-001/AUTH-002 tasks

## 2. Technical Context

### Existing Code

- `src/Core/Auth/` -- Authentificator, Identity, Identities interfaces
- `src/Domain/Profile/Entities/User.php` -- no password field currently
- `src/Infrastructure/Authentification/OpenAuth/` -- LeagueAuthServer, uses plain text password

### Patterns

- Ports & Adapters: interface in Core, implementation in Infrastructure
- Same pattern as `Serializer` (Core) -> `FractalSerializer` (Infrastructure)
- Same pattern as `Dispatcher` (Core) -> `TacticianDispatcher` (Infrastructure)

## 3. Dependencies

- None (foundation task, no blockers)

## 4. Acceptance Criteria

- [ ] `PasswordHasher` interface with `hash()`, `verify()`, `needsRehash()` methods
- [ ] `BcryptPasswordHasher` using `password_hash()` / `password_verify()` / `password_needs_rehash()`
- [ ] DI config binding interface to implementation
- [ ] Unit tests for BcryptPasswordHasher
- [ ] For test and dev environments need weakest algo parameters, for production - strongest
- [ ] All files pass `composer scan:all`
- [ ] No Deptrac violations (Core does not depend on Infrastructure)
