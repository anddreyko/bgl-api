# Documentation: API-001 OpenAPI Export CLI

> Task: bgl-2si
> Completed: 2026-02-26

---

## Commit Message

```
feat(cli): add openapi:export command for JSON spec generation
```

---

## What & Why

Added CLI command to export clean OpenAPI 3.0 specification from PHP-array configs. The project defines API routes and
schemas as PHP arrays with internal extensions (`x-message`, `x-interceptors`, `x-auth`, `x-map`). This command strips
those internal keys and outputs standard-compliant `openapi.json` for Swagger UI, Postman, and code generators.

## Changes Made

**Presentation Layer:**

- Created `src/Presentation/Console/Commands/OpenApiExportCommand.php` -- Symfony Console command `openapi:export`

**Configuration:**

- Created `config/common/console.php` -- registers CLI commands in DI
- Updated `.gitignore` -- added `web/openapi.json` (generated file)

## Technical Details

**How it works:**

1. DI injects merged OpenAPI PHP-array config into command constructor
2. `stripInternalKeys()` recursively removes `x-message`, `x-interceptors`, `x-auth`, `x-map`
3. Encodes to pretty-printed JSON and writes to `web/openapi.json`

**Usage:**

```bash
composer console openapi:export
# Output: web/openapi.json
```

**Key Decisions:**

- Strip function is `public static` for testability as pure function
- Output path hardcoded as `web/openapi.json` -- single deployment target
- Recursive stripping ensures nested internal keys are also removed

## Testing

**Automated Tests:**

- Unit tests for strip logic (pure function):
    - Internal extensions removed
    - Standard OpenAPI fields preserved
    - Nested properties handled correctly
- CLI acceptance tests:
    - Command executes successfully
    - Output file exists and is valid JSON
    - Output contains expected paths
    - No internal extensions in output

## Related Files

| File                                                         | Description                   |
|--------------------------------------------------------------|-------------------------------|
| `src/Presentation/Console/Commands/OpenApiExportCommand.php` | CLI command                   |
| `config/common/console.php`                                  | Command registration          |
| `web/openapi.json`                                           | Generated output (gitignored) |
