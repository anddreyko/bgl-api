# Feature Request: INFRA-003 Phiremock BGG Mock Server

> Task: INFRA-003
> Created: 2026-03-07
> Priority: P1

## Problem

CompositeGames follows "BGG first, local fallback" strategy (plan `tender-tumbling-peacock`, section 6).
Web/acceptance tests run against real Docker infrastructure but BGG API has no mock -- `BggGames` always fails with network error, so `CompositeGames` always falls into the catch branch.

This means:
- The BGG-first path is **never tested** in acceptance tests
- The "BGG unavailable + no local data = 503" path works only by accident
- `config/test/bgg.php` points to `http://bgg-mock:8888` but no such service exists

Env variables `PHIREMOCK_NETWORK_IP`, `PHIREMOCK_EXTERNAL_PORT`, `PHIREMOCK_HTTP_PORT` are already in `.env` but unused.

## Solution

Add Phiremock as a Docker service to mock BGG XML API responses in Web tests.

## Context

- Original implementation existed in branch `develop-v1` (commit `d6f73f33`)
- `BggGames` sends `GET {base_url}/xmlapi2/search?type=boardgame&query=...`
- Response format: BGG XML (`<items total="N"><item id="..." ...>`)
- Phiremock expectations define request matchers + XML responses

## Original Plan (MATES-001 + GAMES-001, section 6)

### Installation

```bash
docker compose run --rm api-php-cli composer require --dev mcustiel/phiremock-codeception-extension mcustiel/phiremock-server
```

### Configuration

Phiremock runs on `bgg-mock:8888` (matches `config/test/bgg.php`).

**Expectations (JSON files)** in `tests/Support/Data/Phiremock/`:

- `bgg-search-results.json` -- BGG XML with games
- `bgg-search-empty.json` -- empty result (`<items total="0"/>`)
- `bgg-unavailable.json` -- 503 response

All expectations return `Content-Type: application/xml` with BGG XML format.

### Docker service

```yaml
bgg-mock:
  image: mcustiel/phiremock
  ports: ["8888:8080"]
  volumes: ["./tests/Support/Data/Phiremock:/var/phiremock/expectations"]
```

### Codeception PhiremockExtension

Custom extension `tests/Support/Extensions/PhiremockExtension.php` wrapping `mcustiel/phiremock-codeception-extension`.
Original from `develop-v1` (commit `d6f73f33`):

- Hooks: `suite.before` -> start Phiremock process, `suite.after` -> stop
- Uses `PhiremockProcessManager` and `ReadinessCheckerFactory` from the library
- Configurable: logs path, suites filter, delay, wait-until-ready with timeout
- Registered in `tests/Web.suite.yml` under `extensions.enabled`

```yaml
# tests/Web.suite.yml
extensions:
  enabled:
    - \Bgl\Tests\Support\Extensions\PhiremockExtension
```

**Note:** Since Phiremock runs as a Docker service (not a local process), the extension may need adaptation:
instead of starting/stopping a process, it should reset expectations via HTTP API (`/__phiremock/reset`)
and load per-suite expectations before each suite run.

### Tests

- `tests/Web/GamesCest.php` -- scenarios:
  - BGG returns results -> 200 with data from DB
  - BGG returns empty XML -> empty result
  - BGG unavailable (503) + no local data -> 500 from API
  - BGG unavailable (503) + local data exists -> 200 stale data

## Dependencies

- CompositeGames, BggGames, config/test/bgg.php -- already implemented (GAMES-001)
- docker-compose.yml, .env -- need modification
- tests/Web/GamesCest.php -- needs new scenarios

## Acceptance Criteria

1. `docker compose up bgg-mock` starts Phiremock container
2. `bgg-mock` is accessible at `http://bgg-mock:8888` from `api-php-cli` network
3. Expectations cover: search with results, empty search, BGG unavailable (503)
4. Web tests verify all CompositeGames paths:
   - BGG returns results -> 200 with games from DB
   - BGG returns empty XML -> empty result
   - BGG unavailable + no local data -> 500
   - BGG unavailable + local data exists -> 200 with stale data
5. `composer test:web` passes with bgg-mock running
