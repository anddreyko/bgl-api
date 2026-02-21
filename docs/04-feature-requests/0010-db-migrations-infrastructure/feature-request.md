# Feature Request: Database Migrations Infrastructure

**Document Version:** 1.0
**Date:** 2026-02-22
**Status:** Open
**Priority:** P0 (Foundation, Sprint 0)

---

## 1. Feature Overview

### Description

Set up Doctrine Migrations infrastructure for the develop branch. Fresh migrations (not ported from main) because
the schema has changed significantly: custom Doctrine types removed, auth_user_access table removed (JWT stateless),
User entity fields changed. Migrations live in `src/Infrastructure/Database/Migrations/`.

### Business Value

- Database schema versioning and reproducible deployments
- Clean schema aligned with new architecture (no legacy custom types)
- Foundation for all domain entities requiring persistence

### Target Users

- Backend Developers: creating and running migrations
- DevOps: automated schema updates in CI/CD

---

## 2. Technical Architecture

### Approach

Install `doctrine/migrations` via composer. Configure namespace `Bgl\Infrastructure\Database\Migrations`, directory
`src/Infrastructure/Database/Migrations/`, table name `migration`. Register console commands for generate, migrate,
diff, status. ADR-015 documents the decision for fresh migrations.

### Integration Points

- Doctrine ORM EntityManager (already configured in `config/common/doctrine.php`)
- PhpMappingDriver (ADR-012) for entity metadata
- Console entry point (`bin/console` or Slim CLI)
- Docker `make init` should run migrations

### Dependencies

- No blocking dependencies (standalone infrastructure task)
- Blocks: AUTH-001 (needs user tables), PLAYS-001 (needs session table), AUTH-006 (needs passkey table)

---

## 3. Directory Structure

```
src/Infrastructure/Database/
    Migrations/
        Version20260222000000.php    # Initial migration (privileges)

config/common/
    migrations.php                   # Doctrine Migrations DI config
```

---

## 4. Code References

| File                              | Relevance                              |
|-----------------------------------|----------------------------------------|
| `config/common/doctrine.php`      | Doctrine EntityManager config          |
| `src/Infrastructure/Database/`    | Existing empty directory for migrations|
| ADR-012                           | PhpMappingDriver for entity mapping    |

---

## 5. Implementation Considerations

### Why Fresh Migrations (Not Ported from Main)

- Main used custom Doctrine types: `DC2Type:id`, `DC2Type:email` -- develop does not have these
- `auth_user` schema changed: added `password_hash`, `confirmed_at`, status values differ
- `auth_user_access` removed entirely (JWT stateless in develop)
- Cleaner to start fresh with correct schema

### Migration Plan by Stage

- Migration 1 (this task): Initial privileges setup
- Migration 2 (AUTH-001): `auth_user`, `auth_email_confirmation_token` tables
- Migration 3 (AUTH-006): `auth_passkey` table
- Migration 4 (PLAYS-001): `records_session` table

---

## 6. Testing Strategy

### Functional Tests

- Migration runs without errors on clean database
- Migration rollback works
- Console commands available and functional

---

## 7. Acceptance Criteria

- [ ] `doctrine/migrations` installed via composer
- [ ] Migration config in `config/common/migrations.php`
- [ ] Migrations directory at `src/Infrastructure/Database/Migrations/`
- [ ] Console commands registered (migrate, generate, diff, status)
- [ ] Initial migration created and runs successfully
- [ ] `composer scan:all` passes

---

## Next Steps

Run `/fr:plan` to generate implementation stages.
