# Master Checklist: Sentry Error Monitoring Integration

**Beads:** bgl-bqx | **FR:** 0046-infra-sentry-integration

## Requirements Quality

| Category           | Status | Notes                                              |
|--------------------|--------|----------------------------------------------------|
| Completeness       | OK     | All requirements present                           |
| Clarity            | OK     | Filtering rules explicit                           |
| Consistency        | OK     | No conflicts                                       |
| Measurability      | OK     | Acceptance criteria verifiable via quality gates   |
| Scenario Coverage  | OK     | DSN present/absent, filtered/unfiltered exceptions |
| Edge Case Coverage | OK     | Empty DSN, missing env var handled                 |
| Non-Functional     | OK     | 100% sample rate, env/release tags                 |

## Overall Progress

- [x] Stage 1: Install and Configure Sentry

## Stages

### Stage 1: Install and Configure Sentry

Infrastructure-only stage. No domain/application code changes. No new test classes (config wiring only).

**Tasks:**

- [x] Install `sentry/sentry` via composer (v4.22.0)
- [x] Create `config/common/sentry.php` with `ignore_exceptions` filter + handler DI
- [x] Update `config/common/logger.php` to add SentryHandler conditionally
- [x] Uncomment `SENTRY_DSN` in `.env`
- [x] Run quality gates: `composer lp:run` OK, `composer ps:run` OK

**Files:**

- `composer.json` (auto via composer require)
- `config/common/sentry.php` (create)
- `config/common/logger.php` (modify)
- `.env` (modify)

**Verification:**

- `composer lp:run` passes
- `composer ps:run` passes

## Consistency Analysis

No issues found.

Metrics: 8 acceptance criteria, 1 stage, 100% coverage
