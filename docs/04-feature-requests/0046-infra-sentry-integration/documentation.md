# Documentation: Sentry Error Monitoring Integration

## What Was Implemented

Sentry PHP SDK integrated into the application for production error monitoring via Monolog handler.

## How It Works

1. **Initialization** (`config/common/sentry.php`):
   - Sentry SDK initializes when `SENTRY_DSN` env var is set (non-empty)
   - Environment tag from `APP_ENV`, release tag from `AppVersion`
   - `ignore_exceptions` filters out `DomainException` and `InvalidArgumentException`

2. **Monolog Integration** (`config/common/logger.php`):
   - `Sentry\Monolog\Handler` added to Monolog stack at `error` level
   - Only added when Sentry is initialized (DSN present)
   - Existing `StreamHandler` (stderr) unchanged

3. **Error Flow**:
   - `Logging` aspect catches exceptions and logs at `error` level
   - Monolog forwards to both stderr and Sentry handler
   - Sentry SDK filters: `DomainException` / `InvalidArgumentException` dropped, everything else sent

## Configuration

| Env Variable | Required | Description |
|-------------|----------|-------------|
| `SENTRY_DSN` | No | Sentry DSN. Empty or missing = Sentry disabled |
| `APP_ENV` | No | Environment tag (defaults to `prod`) |

## Files Changed

- `config/common/sentry.php` -- new: Sentry init + DI bindings
- `config/common/logger.php` -- modified: conditional SentryHandler
- `.env` -- modified: `SENTRY_DSN` uncommented
- `composer.json` / `composer.lock` -- added `sentry/sentry ^4.22`
