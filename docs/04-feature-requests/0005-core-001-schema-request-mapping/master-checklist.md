# Master Checklist: Schema-Based Request Mapping (CORE-001 Part 2)

> Status: **DONE**

---

## Stage 1: Core Contracts (~20min)

**Dependencies:** None

- [x] Create `src/Presentation/Api/Interceptors/Interceptor.php` -- interface with `process(ServerRequestInterface): ServerRequestInterface`
- [x] Create `src/Core/Http/SchemaMapper.php` -- interface with `map(ServerRequestInterface, array $schema): array`
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File | Action |
|------|--------|
| `src/Presentation/Api/Interceptors/Interceptor.php` | CREATE |
| `src/Core/Http/SchemaMapper.php` | CREATE |

---

## Stage 2: RouteMap (~30min)

**Dependencies:** Stage 1

- [x] Create `src/Presentation/Api/MatchedOperation.php` -- VO with messageClass, interceptors, pathParams, schema
- [x] Create `src/Presentation/Api/RouteMap.php` -- reads OpenAPI config, matches method+path to operation
- [x] Support path params via regex (e.g. `/v1/auth/confirm/{token}`)
- [x] Read `x-message`, `x-interceptors`, `x-target` from OpenAPI config
- [x] Return `null` when no route matches
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File | Action |
|------|--------|
| `src/Presentation/Api/MatchedOperation.php` | CREATE |
| `src/Presentation/Api/RouteMap.php` | CREATE |

---

## Stage 3: InterceptorPipeline + SchemaMapper Implementation (~30min)

**Dependencies:** Stage 1

- [x] Create `src/Presentation/Api/InterceptorPipeline.php` -- resolves interceptors from container, executes chain
- [x] Create `src/Infrastructure/Http/OpenApiSchemaMapper.php` -- implements SchemaMapper
- [x] Extract body, query, path params based on x-target schema config
- [x] Wire DI in `config/common/http.php`
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File | Action |
|------|--------|
| `src/Presentation/Api/InterceptorPipeline.php` | CREATE |
| `src/Infrastructure/Http/OpenApiSchemaMapper.php` | CREATE |
| `config/common/http.php` | CREATE |

---

## Stage 4: ApiAction (~30min)

**Dependencies:** Stages 2, 3

- [x] Create `src/Presentation/Api/ApiAction.php` -- single entry point for all API routes
- [x] Match route via RouteMap
- [x] Run InterceptorPipeline
- [x] Map request data via SchemaMapper
- [x] Create Command/Query from mapped data
- [x] Dispatch via Dispatcher
- [x] Serialize result via Serializer
- [x] Handle domain exceptions -> ErrorResponse with appropriate HTTP status
- [x] Return 404 for unknown routes
- [x] Update `config/common/openapi/ping.php` with x-message extension
- [x] Wire ApiAction in DI config
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File | Action |
|------|--------|
| `src/Presentation/Api/ApiAction.php` | CREATE |
| `config/common/openapi/ping.php` | MODIFY |
| `config/common/api-action.php` | CREATE |

---

## Stage 5: Tests (~40min)

**Dependencies:** Stage 4

- [x] Unit test: RouteMap -- matching, path params, no match (7 tests)
- [x] Unit test: InterceptorPipeline -- execution order, empty pipeline (3 tests)
- [x] Unit test: OpenApiSchemaMapper -- body, query, path extraction, casts (10 tests)
- [x] Functional test: ApiAction -- happy path with Ping handler (3 tests)
- [x] Verify: `composer test:unit && composer test:func`

### Files

| File | Action |
|------|--------|
| `tests/Unit/Presentation/Api/RouteMapCest.php` | CREATE |
| `tests/Unit/Presentation/Api/InterceptorPipelineCest.php` | CREATE |
| `tests/Unit/Infrastructure/Http/OpenApiSchemaMapperCest.php` | CREATE |
| `tests/Functional/Api/ApiActionCest.php` | CREATE |

---

## Stage 6: Final Validation (~15min)

**Dependencies:** All previous stages

- [x] Run `composer lp:run` -- passed (168 files)
- [x] Run `composer ps:run` -- 7 pre-existing errors only (no new errors)
- [x] Run `composer dt:run` -- 0 violations
- [x] Run `composer test:unit` -- 59 tests passed
- [x] Run `composer test:func` -- 5/6 passed (1 pre-existing failure: PingHandlerCest)
- [x] Verify `web/index.php` uses ApiAction correctly
- [x] Review: code follows PSR-12, `declare(strict_types=1)` in all files

### Additional fixes

- [x] Fixed `config/common/serializer.php`: changed string alias to `DI\get()` for proper PHP-DI resolution

---

## Code References

| File | What to Learn |
|------|---------------|
| `src/Core/Messages/Dispatcher.php` | Message dispatch interface |
| `src/Core/Serialization/Serializer.php` | Serialization pattern |
| `src/Presentation/Api/V1/Responses/ErrorResponse.php` | Error response structure |
| `config/common/openapi/ping.php` | OpenAPI config format |
| `web/index.php` | Entry point integration |
| `docs/03-decisions/011-unified-route-configuration.md` | ADR for x-extensions |
