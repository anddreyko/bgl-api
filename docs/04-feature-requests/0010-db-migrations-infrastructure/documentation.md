# Documentation: Database Migrations Infrastructure

> FR: 0010-db-migrations-infrastructure
> Completed: 2026-02-23

## Summary

Set up Doctrine Migrations for database schema version control. Migrations are stored in `src/Infrastructure/Database/Migrations/` and managed through console commands via Make targets.

## Key Files

| File | Purpose |
|------|---------|
| `config/common/migrations.php` | Doctrine Migrations DI configuration |
| `src/Infrastructure/Database/Migrations/Version20260222194722.php` | Initial migration (auth_user table) |
| `src/Infrastructure/Database/Migrations/Version20260223091859.php` | Second migration |
| `src/Infrastructure/Database/Migrations/Version20260223092704.php` | Third migration |

## How It Works

Doctrine Migrations provides:

1. Version-controlled database schema changes
2. Up/down migration support
3. Automated diff generation from ORM mappings
4. Migration status tracking via `migration` table

Console commands:
- `make migrate` - Run pending migrations
- `make migrate-gen` - Generate migration from ORM diff
- `make migrate-empty` - Create empty migration class
- `make validate-schema` - Validate ORM schema against database

Configuration:
- Namespace: `Bgl\Infrastructure\Database\Migrations`
- Directory: `src/Infrastructure/Database/Migrations/`
- Table name: `migration`

## Testing

Migrations are tested through:
- Integration tests that require database
- Functional tests with fixtures
- Schema validation checks
