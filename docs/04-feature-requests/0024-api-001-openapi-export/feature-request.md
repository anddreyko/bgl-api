# Feature Request: API-001 OpenAPI Export CLI

**Task:** bgl-2si
**Date:** 2026-02-23
**Status:** Done
**Priority:** P2
**Depends on:** bgl-aqk (CORE-009)

---

## 1. Feature Overview

### Description

CLI command to export clean OpenAPI 3.0 specification from PHP-array configs. Strips internal extensions (`x-message`, `x-interceptors`, `x-auth`, `x-map`) and writes standard-compliant `openapi.json` to `public/` directory. Enables Swagger UI integration and external API documentation.

### Business Value

- Machine-readable API spec for Swagger UI, Postman, code generators
- Single source of truth: PHP configs define both routing and documentation
- Clean export: no internal extensions leak to consumers

---

## 2. Technical Architecture

### Approach

- Symfony Console command in `Presentation/Console/Commands/`
- Load all OpenAPI PHP-array configs
- Merge into single spec with `openapi`, `info`, `paths` sections
- Recursively strip keys starting with `x-message`, `x-interceptors`, `x-auth`, `x-map`
- Write JSON to `public/openapi.json`

### Integration Points

- `config/common/openapi/*.php` -- source configs
- `public/openapi.json` -- output
- `config/common/console.php` -- register command

---

## 3. Directory Structure

```
src/Presentation/Console/Commands/
    OpenApiExportCommand.php    # CLI command

public/
    openapi.json                # Generated output (gitignored)
```

---

## 4. Testing Strategy

### Unit Tests

- Strip logic: verify internal extensions removed, standard fields preserved
- Output: valid JSON, valid OpenAPI 3.0 structure

### CLI Tests

- Command executes successfully
- Output file created with expected content

---

## 5. Acceptance Criteria

- [ ] CLI command `openapi:export` registered
- [ ] Loads all OpenAPI PHP configs
- [ ] Strips `x-message`, `x-interceptors`, `x-auth`, `x-map`
- [ ] Writes valid OpenAPI 3.0 JSON to `public/openapi.json`
- [ ] `composer scan:all` passes
