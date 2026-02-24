# Master Checklist: DB-MIGRATIONS Infrastructure

**Task:** bgl-2vv
**Size:** Small
**Approach:** Install + Configure + Verify

---

## Stage 1: Install and Configure

- [x] Install `doctrine/migrations` via `docker compose run --rm api-php-cli composer require doctrine/migrations`
- [x] Create `config/common/migrations.php` with DI definitions:
  - Migration namespace: `Bgl\Infrastructure\Database\Migrations`
  - Migration directory: `src/Infrastructure/Database/Migrations/`
  - Table name: `migration`
  - Register `SingleManagerProvider` for console
  - Register migration commands (migrate, diff, generate, status)
- [x] Create `src/Infrastructure/Database/Migrations/` directory (empty, with .gitkeep)
- [x] Verify `cli/app` console entry point works with migration commands

## Stage 2: Initial Migration

- [x] Generate initial migration via `make migrate-gen` or create manually
  - Should create base schema or be a no-op if schema is clean
- [x] Run `make migrate` -- migration executes successfully
- [x] Verify `migration` table created in database
- [x] Verify `make validate-schema` passes

## Stage 3: Quality Gates

- [x] Run `composer lp:run` -- passes
- [x] Run `composer ps:run` -- passes
- [x] Run `composer dt:run` -- passes
- [x] Run `composer scan:all` -- passes

## Validation Criteria

- doctrine/migrations installed in composer.json
- Console commands available: `migrations:migrate`, `migrations:diff`, `migrations:generate`, `migrations:status`
- Migrations directory exists at correct location
- Initial migration runs without errors
- No Psalm suppressions added
