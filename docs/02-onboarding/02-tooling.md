# Development Tools

All development tools are isolated via **vendor-bin** to avoid dependency conflicts. See the decision
details: [ADR-005](../03-decisions/005-excluded-tools.md).

---

## Running Commands

Developers can use any convenient method.

### Composer Scripts (Recommended)

```bash
composer cs             # PHP-CS-Fixer — fix code style
composer rc             # Rector — automated refactoring
composer lp             # PHP Lint — syntax check
composer ps             # Psalm — static analysis
composer dt             # Deptrac — architectural dependency check

composer scan:style     # PHP-CS-Fixer + Rector (modifies code)
composer scan:php       # Lint + Psalm
composer scan:depend    # Deptrac + Composer dependencies
composer scan:all       # scan:php + scan:depend + test:all (without scan:style)
```

### Makefile (Docker wrapper)

```bash
make cs    # runs composer cs:fix inside Docker
make ps    # runs composer ps:run inside Docker
make dt    # runs composer dt:run inside Docker
make scan  # runs composer scan:all inside Docker
```

### Direct Invocation

```bash
vendor-bin/psalm/vendor/bin/psalm
vendor-bin/php-cs-fixer/vendor/bin/php-cs-fixer fix
```

---

## Main Commands

### Initialization and Environment

| Command                    | Purpose                         |
|----------------------------|---------------------------------|
| `make init`                | Initialize Docker containers    |
| `make up` / `make down`    | Start / stop containers         |
| `composer install`         | Install production dependencies |
| `composer bin all install` | Install all dev tools           |

### Code Quality

| Command       | Tool         | Purpose                        |
|---------------|--------------|--------------------------------|
| `composer cs` | PHP-CS-Fixer | Auto-fix code style (PSR-12)   |
| `composer rc` | Rector       | Automated refactoring          |
| `composer lp` | PHP Lint     | PHP syntax check               |
| `composer ps` | Psalm        | Static type analysis           |
| `composer dt` | Deptrac      | Architectural dependency check |

### Check Groups

| Command                | Contents                          | Purpose                                  |
|------------------------|-----------------------------------|------------------------------------------|
| `composer scan:style`  | PHP-CS-Fixer + Rector             | Fix style (modifies code)                |
| `composer scan:php`    | Lint + Psalm                      | Syntax and type check                    |
| `composer scan:depend` | Deptrac + Composer deps           | Architecture and dependencies            |
| `composer scan:all`    | scan:php + scan:depend + test:all | Full pre-push check (without scan:style) |

The `scan:all` command intentionally excludes `scan:style` since it modifies code. Run `scan:style` separately first,
then `scan:all` for verification.

### Testing

| Command                  | Purpose                  |
|--------------------------|--------------------------|
| `composer test:unit`     | Unit tests               |
| `composer test:func`     | Functional tests         |
| `composer test:intg`     | Integration tests        |
| `composer test:web`      | Acceptance API tests     |
| `composer test:cli`      | Acceptance CLI tests     |
| `composer test:all`      | All tests                |
| `composer test:coverage` | Generate coverage report |
| `composer in`            | Mutation testing         |

### Database

| Command              | Purpose                                  |
|----------------------|------------------------------------------|
| `make wait-db`       | Wait for database readiness              |
| `make migrate`       | Run all pending migrations               |
| `make migrate-gen`   | Generate migration diff from ORM mapping |
| `make migrate-empty` | Generate empty migration class           |
| `make validate-schema` | Validate ORM schema against database   |
| `make load-fixtures` | Load fixtures into database              |

CLI application (`cli/app`) provides direct access to Doctrine ORM and Migrations commands:

```bash
# Inside Docker container (via make sh)
php cli/app migrations:status       # Show migration status
php cli/app migrations:migrate      # Run pending migrations
php cli/app migrations:diff         # Generate migration from mapping diff
php cli/app migrations:generate     # Generate empty migration
php cli/app migrations:execute      # Execute specific migration up/down
php cli/app orm:validate-schema     # Validate entity mapping
```

Migrations are stored in `src/Infrastructure/Database/Migrations/` with namespace `Bgl\Infrastructure\Database\Migrations`. Configuration: `config/common/migrations.php`. Migration table: `migration`.

### Versioning

| Command             | Purpose                           |
|---------------------|-----------------------------------|
| `composer vi`       | Auto-detect and increment version |
| `composer vi:major` | Increment major version           |
| `composer vi:minor` | Increment minor version           |
| `composer vi:patch` | Increment patch version           |

---

## Docker Services

| Service        | Image                | Purpose                      | Healthcheck               |
|----------------|----------------------|------------------------------|---------------------------|
| `api`          | nginx:1.29-alpine    | Reverse proxy                | `wget /ping`              |
| `api-php-fpm`  | php:8.4-fpm-alpine   | PHP-FPM application server   | Process check (PID 1)     |
| `api-php-cli`  | php:8.4-cli-alpine   | CLI runner for commands       | --                        |
| `api-ci`       | production Dockerfile | CI/CD runner                 | --                        |
| `db-postgres`  | postgres:15.2-alpine | PostgreSQL database           | `pg_isready`              |
| `redis`        | redis:7-alpine       | Redis cache/session store    | `redis-cli ping`          |

Service startup order is enforced via `depends_on` with health conditions: `db-postgres` and `redis` must be healthy before `api-php-fpm` starts, and `api-php-fpm` must be healthy before `api` (nginx) starts.

---

## Tools in vendor-bin

| Directory                  | Tool         | Purpose                    |
|----------------------------|--------------|----------------------------|
| `vendor-bin/psalm/`        | Psalm        | Static type analysis       |
| `vendor-bin/php-cs-fixer/` | PHP-CS-Fixer | Code style (PSR-12)        |
| `vendor-bin/rector/`       | Rector       | Automated refactoring      |
| `vendor-bin/codeception/`  | Codeception  | Testing framework          |
| `vendor-bin/deptrac/`      | Deptrac      | Architectural dependencies |
| `vendor-bin/infection/`    | Infection    | Mutation testing           |

### Update Individual Tool

```bash
composer bin psalm update
composer bin rector update
```

### Install All Tools

```bash
composer bin all install
```

---

## Pre-Commit Workflow

```bash
# 1. Fix code style and apply refactoring
composer scan:style

# 2. Full check: syntax, types, architecture, tests
composer scan:all

# 3. Commit
git commit -m "feat(plays): add player validation"
```

---

## IDE Integration

### PHPStorm

Psalm and PHP-CS-Fixer can be configured as External Tools or File Watchers.

### VS Code

Use PHP Intelephense and Psalm extensions, and configure tasks for running composer commands.
