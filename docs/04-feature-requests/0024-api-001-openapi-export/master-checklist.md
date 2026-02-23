# Master Checklist: API-001 OpenAPI Export CLI

> Task: bgl-2si
> Created: 2026-02-23
> Depends on: bgl-aqk (CORE-009)

## Overview

**Overall Progress:** 0 of 2 stages completed
**Current Stage:** Stage 1

---

## Stage 1: Export Command (~30min)

**Dependencies:** CORE-009 completed

- [ ] Create `src/Presentation/Console/Commands/OpenApiExportCommand.php`:
  - Symfony Console command, name `openapi:export`
  - Load all `config/common/openapi/*.php` configs
  - Merge into single OpenAPI 3.0 structure with `openapi`, `info`, `paths`
  - Recursively strip internal keys: `x-message`, `x-interceptors`, `x-auth`, `x-map`
  - Write JSON (pretty-printed) to `public/openapi.json`
  - Output success message with file path
- [ ] Register command in `config/common/console.php`
- [ ] Add `public/openapi.json` to `.gitignore` (generated file)
- [ ] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: Tests + Final Validation (~20min)

**Dependencies:** Stage 1

- [ ] Write unit test for strip logic:
  - Internal extensions removed (`x-message`, `x-interceptors`, `x-auth`, `x-map`)
  - Standard OpenAPI fields preserved (`summary`, `requestBody`, `parameters`)
  - Nested properties not affected
- [ ] Write CLI test:
  - Command executes successfully
  - Output file exists and is valid JSON
  - Output contains expected paths
  - Output does not contain internal extensions
- [ ] Run `composer scan:all` (MANDATORY)
- [ ] Run `composer test:all`

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `src/Presentation/Console/Commands/OpenApiExportCommand.php` | CREATE | 1 |
| `config/common/console.php` | MODIFY | 1 |
| `.gitignore` | MODIFY | 1 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Not Started | - | |
| 2 | Not Started | - | |
