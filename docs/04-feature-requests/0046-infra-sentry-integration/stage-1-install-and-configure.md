# Stage 1: Install and Configure Sentry

## Overview

Install Sentry PHP SDK and wire it into Monolog via DI config. Filter out DomainException and InvalidArgumentException
at the Sentry SDK level via `before_send`.

## Dependencies

None (single stage).

## Implementation Steps

### 1. Install package

```bash
docker compose run --rm api-php-cli composer require sentry/sentry
```

### 2. Create `config/common/sentry.php`

- Call `\Sentry\init()` with:
    - `dsn` from `SENTRY_DSN` env var
    - `environment` from `APP_ENV` env var
    - `release` from `AppVersion` DI service
    - `before_send` callback that returns `null` for `DomainException` and `InvalidArgumentException`
- Register `Sentry\Monolog\Handler` in DI (level: `error`)
- Guard: if `SENTRY_DSN` is empty/false, skip init entirely

### 3. Update `config/common/logger.php`

- Inject `Sentry\Monolog\Handler` from container (if available)
- Push it to Monolog stack alongside existing StreamHandler
- Use `ContainerInterface::has()` to conditionally add handler

### 4. Update `.env`

- Uncomment `SENTRY_DSN` line

## Code References

| File                                  | Purpose                                           |
|---------------------------------------|---------------------------------------------------|
| `config/common/logger.php`            | Current Monolog setup (StreamHandler to stderr)   |
| `config/common/app-version.php`       | AppVersion DI definition (for release tag)        |
| `src/Core/AppVersion.php`             | AppVersion value object                           |
| `src/Application/Aspects/Logging.php` | Logs errors at `error` level -- no changes needed |

## Completion Criteria

- [ ] `sentry/sentry` in composer.json
- [ ] `config/common/sentry.php` exists with init + filter + handler
- [ ] `config/common/logger.php` conditionally adds SentryHandler
- [ ] `SENTRY_DSN` uncommented in `.env`
- [ ] `composer lp:run` passes
- [ ] `composer ps:run` passes

## Potential Issues

- Psalm may not know Sentry types -- may need `@psalm-type` annotations or stub
- `Sentry\Monolog\Handler` constructor signature may vary by SDK version -- check after install
