# ADR-014: Twelve-Factor App Principles

## Date: 2026-02-26

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

BoardGameLog API is deployed as a containerized service. As the project matures toward production readiness, we need
a documented set of operational principles to guide infrastructure and deployment decisions consistently.

The [Twelve-Factor App](https://12factor.net) methodology provides a well-established framework for building
cloud-native services. An audit of the current codebase was performed to identify compliance gaps.

---

### Decision

Adopt the Twelve-Factor App methodology as the operational standard for BoardGameLog API.

---

### Current Compliance

#### Fully Compliant

**I. Codebase** -- One repository, multiple deploys via environment configuration.

**II. Dependencies** -- All dependencies declared in `composer.json` + `composer.lock`. Docker isolates the runtime.
Dev tools isolated via `bamarni/composer-bin-plugin` in `vendor-bin/`.

**IV. Backing Services** -- PostgreSQL 15.2 and Redis 7 connected via environment variables (`DB_HOST`, `DB_USER`,
`DB_PASS`, `DB_PORT`, `DB_NAME`). Swappable without code changes. Redis declared in `docker-compose.yml` with
healthcheck, not yet used by application code.

**VI. Processes** -- Stateless design. JWT authentication, no PHP sessions, no sticky state. Persistent data in
PostgreSQL only.

**VII. Port Binding** -- Self-contained: nginx + php-fpm in container, port exposed via `APP_EXTERNAL_PORT` env var.
Production image exposes port 10000.

**X. Dev/Prod Parity** -- Same backing services (PostgreSQL 15.2, Redis 7) in all environments. Docker ensures
consistent runtime. Acceptable differences: Xdebug (dev only), OPcache validation timestamps, dev-dependencies.

**XII. Admin Processes** -- Migrations, fixtures, schema validation run via `docker compose run --rm api-php-cli`
using the same codebase, config, and dependencies as web processes.

**XI. Logs** -- Monolog writes to `php://stderr` (`config/common/logger.php`). Nginx writes to stdout/stderr.
The execution environment handles log collection and routing.

**IX. Disposability** -- Production entrypoint (`.docker/prod/entrypoint.sh`) traps SIGTERM/SIGINT/SIGQUIT and
propagates signals to both nginx and php-fpm for clean shutdown of in-flight requests.

#### Partially Compliant (Acceptable for Current Scale)

**III. Config** -- Environment variables used for all credentials and service connections (DB_HOST, DB_USER, DB_PASS,
DB_PORT, DB_NAME, JWT_SECRET, WEBAUTHN_RP_ID, WEBAUTHN_RP_NAME, etc.). `.env` is gitignored. Some internal paths
(cache directories) remain in PHP config files -- acceptable since they do not vary between deploys.

**V. Build, Release, Run** -- Multi-stage Dockerfile (`.docker/prod/Dockerfile`) with `production` and `ci` targets.
`production` target: lean image with nginx + php-fpm + app code (no dev deps, no xdebug). `ci` target: extends
production + dev deps for scanning. No automated CI/CD pipeline yet.

**VIII. Concurrency** -- PHP-FPM handles HTTP concurrency. Default pool configuration used. No background workers
yet (not needed at current scale). Explicit pool tuning deferred until production load profiling.

---

### Consequences

- Application logs route through stderr via Monolog, enabling centralized log collection on any platform
- Graceful shutdown via `entrypoint.sh` signal trapping prevents request loss during deployments
- Multi-stage Docker build separates production (lean) from CI (with dev tools) targets
- Redis infrastructure is ready but not yet consumed by application code
- CI/CD pipeline (GitHub Actions) to be implemented as a separate task
- PHP-FPM pool tuning to be addressed when production load data is available
