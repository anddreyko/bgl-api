# Master Checklist: API-001 OpenAPI Export CLI

> Task: bgl-2si
> Created: 2026-02-23
> Depends on: bgl-aqk (CORE-009)

## Overview

**Overall Progress:** 2 of 2 stages completed
**Current Stage:** Done

---

## Stage 1: Export Command (~30min)

**Dependencies:** CORE-009 completed

- [x] Create `src/Presentation/Console/Commands/OpenApiExportCommand.php`:
  - Symfony Console command, name `openapi:export`
  - Load all `config/common/openapi/*.php` configs
  - Merge into single OpenAPI 3.0 structure with `openapi`, `info`, `paths`
  - Recursively strip internal keys: `x-message`, `x-interceptors`, `x-auth`, `x-map`
  - Write JSON (pretty-printed) to `web/openapi.json`
  - Output success message with file path
- [x] Register command in `config/common/console.php`
- [x] Add `web/openapi.json` to `.gitignore` (generated file)
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Tests + Final Validation (~20min)

**Dependencies:** Stage 1

- [x] Write unit test in `tests/Unit/` for strip logic (pure function, no deps -> Unit suite per ADR-015):
  - Internal extensions removed (`x-message`, `x-interceptors`, `x-auth`, `x-map`)
  - Standard OpenAPI fields preserved (`summary`, `requestBody`, `parameters`)
  - Nested properties not affected
- [x] Write CLI acceptance test in `tests/Cli/` (Presentation layer -> Cli suite per ADR-015):
  - Command executes successfully
  - Output file exists and is valid JSON
  - Output contains expected paths
  - Output does not contain internal extensions
- [x] Run `composer scan:all` (MANDATORY)
- [x] Run `composer test:all`

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Presentation/Console/Commands/OpenApiExportCommand.php` | CREATE | 1 |
| `config/common/console.php` | CREATE | 1 |
| `cli/app` | MODIFY | 1 |
| `.gitignore` | MODIFY | 1 |
| `tests/Unit/Presentation/Console/Commands/OpenApiExportCommandCest.php` | CREATE | 2 |
| `tests/Cli/OpenApiExportCest.php` | CREATE | 2 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-02-26 | Command created, registered in DI, .gitignore updated |
| 2 | Done | 2026-02-26 | 5 unit tests + 4 CLI acceptance tests, all passing |
