# Master Checklist: INFRA-003 Phiremock BGG Mock Server

> Feature: INFRA-003 Phiremock BGG Mock Server
> Created: 2026-03-07

## Overview

**Overall Progress:** 3 of 3 stages completed

**Current Stage:** Done

---

## Stage 1: Docker + Phiremock Setup

**Dependencies:** None

- [x] Add `bgg-mock` service to `docker-compose.yml`
  - Built from `.docker/dev/phiremock/Dockerfile` (PHP 8.4 + phiremock-server)
  - Volumes: `./tests/Support/Data/phiremock:/opt/phiremock/expectation-files:cached`
  - Port: `${PHIREMOCK_EXTERNAL_PORT}:${PHIREMOCK_HTTP_PORT}` (8086)
  - Network: `bglapinet` with IP `${PHIREMOCK_NETWORK_IP}`, alias `bgg-mock`
  - Healthcheck: `wget -O /dev/null http://127.0.0.1:8086/__phiremock/expectations`
- [x] `.env` vars already exist (`PHIREMOCK_NETWORK_IP`, `PHIREMOCK_EXTERNAL_PORT`, `PHIREMOCK_HTTP_PORT`)
- [x] Fix `config/test/bgg.php` and create `config/dev/bgg.php`: `base_url` = `http://bgg-mock:8086`
- [x] Install: `composer require --dev mcustiel/phiremock-client`
- [x] Verify: `docker compose up bgg-mock -d` starts successfully
- [x] Verify: API accessible from `api-php-fpm` via `http://bgg-mock:8086`

---

## Stage 2: Expectations

**Dependencies:** Stage 1

- [x] Create `tests/Support/Data/phiremock/` directory for expectation files
- [x] Create expectation: `bgg-search-catan.json` (URL matches `query=catan`, returns 200 XML with 2 games)
- [x] Create expectation: `bgg-search-empty.json` (URL matches `query=zzz_nonexistent`, returns 200 empty XML)
- [x] Create expectation: `bgg-search-unavailable.json` (URL matches `query=unavailable_trigger`, returns 503)
- [x] Static expectations loaded on container startup (no Codeception extension needed)
- [x] Verify: all 3 expectations load on Phiremock startup

---

## Stage 3: Web Tests + Validation

**Dependencies:** Stage 2

- [x] Extend `tests/Web/GamesCest.php` with 4 Phiremock scenarios:
  - `testSearchGamesBggReturnsResults`: search "catan" -> BGG returns XML -> 200 with 2 games
  - `testSearchGamesBggReturnsEmpty`: search "zzz_nonexistent" -> BGG returns empty -> 200 with 0 items
  - `testSearchGamesWhenBggUnavailableAndNoLocal`: trigger 503 from BGG, no local data -> 500
  - `testSearchGamesWhenBggUnavailableWithLocalFallback`: pre-insert game in DB, trigger 503 -> 200 with stale data
- [x] Fix CompositeGames: add `Transactor::flush()` after BGG upsert to make entities visible in DQL
- [x] Run `composer test:web` -- all 22 tests pass
- [x] Run `composer ps:run` -- no Psalm errors
- [x] Run `composer scan:all` -- passed (MSI 82%)

---

## Quick Reference

### Files Overview

| File | Action | Stage |
|------|--------|-------|
| `docker-compose.yml` | MODIFY | 1 |
| `.docker/dev/phiremock/Dockerfile` | CREATE (optional) | 1 |
| `config/test/bgg.php` | MODIFY | 1 |
| `composer.json` | MODIFY (require-dev) | 1 |
| `tests/Support/Data/phiremock/bgg-search-catan.json` | CREATE | 2 |
| `tests/Support/Data/phiremock/bgg-search-empty.json` | CREATE | 2 |
| `tests/Support/Data/phiremock/bgg-search-unavailable.json` | CREATE | 2 |
| `tests/Web.suite.yml` | MODIFY | 2 |
| `tests/Web/GamesCest.php` | MODIFY | 3 |

### Key Decisions

- Phiremock chosen over WireMock (PHP ecosystem, Codeception integration)
- Static expectation files (loaded on startup) vs programmatic (per-test via API)
- BGG XML format must match what BggGames parser expects (SimpleXMLElement)
- Port alignment: `.env` has 8086, `config/test/bgg.php` has 8888 -- needs reconciliation

---

### Additional Changes (discovered during implementation)

| File | Action | Stage |
|------|--------|-------|
| `config/dev/bgg.php` | CREATE | 1 |
| `src/Infrastructure/Persistence/CompositeGames.php` | MODIFY | 3 |
| `config/common/persistence.php` | MODIFY | 3 |
| `deptrac.yml` | MODIFY | 3 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-03-07 | Docker + Phiremock setup |
| 2 | Done | 2026-03-07 | Static expectations (3 JSON files) |
| 3 | Done | 2026-03-07 | 4 new Web tests, CompositeGames flush fix, deptrac exclusion |
