# Master Checklist: DB-MIGRATIONS Infrastructure

**Task:** bgl-2vv
**Size:** Small
**Approach:** Install + Configure + Verify

---

## Stage 1: Install and Configure

- [ ] Install `doctrine/migrations` via `docker compose run --rm api-php-cli composer require doctrine/migrations`
- [ ] Create `config/common/migrations.php` with DI definitions:
  - Migration namespace: `Bgl\Infrastructure\Database\Migrations`
  - Migration directory: `src/Infrastructure/Database/Migrations/`
  - Table name: `migration`
  - Register `SingleManagerProvider` for console
  - Register migration commands (migrate, diff, generate, status)
- [ ] Create `src/Infrastructure/Database/Migrations/` directory (empty, with .gitkeep)
- [ ] Verify `cli/app` console entry point works with migration commands

## Stage 2: Initial Migration

- [ ] Generate initial migration via `make migrate-gen` or create manually
  - Should create base schema or be a no-op if schema is clean
- [ ] Run `make migrate` -- migration executes successfully
- [ ] Verify `migration` table created in database
- [ ] Verify `make validate-schema` passes

## Stage 3: Quality Gates

- [ ] Run `composer lp:run` -- passes
- [ ] Run `composer ps:run` -- passes
- [ ] Run `composer dt:run` -- passes
- [ ] Run `composer scan:all` -- passes

## Validation Criteria

- doctrine/migrations installed in composer.json
- Console commands available: `migrations:migrate`, `migrations:diff`, `migrations:generate`, `migrations:status`
- Migrations directory exists at correct location
- Initial migration runs without errors
- No Psalm suppressions added
