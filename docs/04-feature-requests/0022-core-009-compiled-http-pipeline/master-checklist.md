# Master Checklist: CORE-009 Compiled Route Map + Hydrator Mapper

> Task: bgl-aqk
> Created: 2026-02-23

## Overview

**Overall Progress:** 4 of 4 stages completed
**Current Stage:** Done

---

## Stage 1: Install Package + Simplify OpenAPI Configs (~30min)

**Dependencies:** None

- [x] Install `eventsauce/object-hydrator` via `docker compose run --rm api-php-cli composer require eventsauce/object-hydrator`
- [x] Update `config/common/openapi/auth.php`:
  - Remove `x-target` and `x-source` from `sign-out` route body properties
  - Add `'x-auth' => ['userId']` at operation level for `sign-out`
- [x] Update `config/common/openapi/plays.php`:
  - Remove `x-target` from `{id}` path parameter (CloseSession), add `'x-map' => ['id' => 'sessionId']` at operation level
  - Remove `x-target`/`x-source` from `userId` body properties
  - Remove `x-target` with `|datetime` cast from `finishedAt` (EventSauce handles from constructor type)
  - Add `'x-auth' => ['userId']` at operation level for both `post` (OpenSession) and `patch` (CloseSession)
- [x] Update `config/common/openapi/user.php`:
  - Remove `x-target` from `{id}` path parameter, add `'x-map' => ['id' => 'userId']` at operation level
- [x] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: CompiledRouteMap + CompiledOperation (~40min)

**Dependencies:** Stage 1

- [x] Create `src/Presentation/Api/CompiledOperation.php` -- readonly VO:
  - `string $messageClass` (class-string of Message)
  - `array $interceptors` (list of Interceptor class-strings)
  - `array $authParams` (from x-auth, e.g. `['userId']`)
  - `array $paramMap` (from x-map, e.g. `['id' => 'sessionId']`)
  - `array $openApiSchema` (raw OpenAPI operation for validation/export)
- [x] Create `src/Presentation/Api/CompiledRouteMap.php`:
  - Constructor receives merged OpenAPI paths config
  - Boot-time compilation: split routes into static (no `{}`) and dynamic (with `{}`)
  - Static map: `'METHOD /path'` => CompiledOperation (HashMap, O(1))
  - Dynamic map: single combined regex with `(*MARK:key)` groups
  - `match(string $method, string $path): ?MatchResult` -- returns CompiledOperation + pathParams
- [x] Create `src/Presentation/Api/MatchResult.php` -- readonly VO:
  - `CompiledOperation $operation`
  - `array $pathParams`
- [x] Write unit tests for CompiledRouteMap:
  - Static route match (GET /v1/auth/sign-up)
  - Dynamic route match (GET /v1/user/{id})
  - No match returns null
  - Method mismatch returns null
  - Path params extracted correctly
- [x] Verify: `composer lp:run && composer ps:run && composer test:unit`

---

## Stage 3: HydratorMapper + ApiAction Update (~40min)

**Dependencies:** Stage 2

- [x] Update `src/Core/Http/SchemaMapper.php` interface:
  - Changed signature to accept pathParams, authParams, paramMap instead of raw schema array
- [x] Create `src/Infrastructure/Http/HydratorMapper.php`:
  - Collects body params, query params
  - Applies path param renames from paramMap
  - Injects auth params from request attributes (`auth.{paramName}`)
  - Returns flat array for hydration
- [x] Update `src/Presentation/Api/ApiAction.php`:
  - Replace `RouteMap` with `CompiledRouteMap`
  - Use `CompiledRouteMap::match()` returning `MatchResult`
  - Pass pathParams, authParams, paramMap to `SchemaMapper::map()`
  - Use `ObjectMapper::hydrateObject($messageClass, $data)` instead of `new $messageClass(...$data)`
- [x] Update DI config (`config/common/api-action.php`):
  - Register CompiledRouteMap factory (replaces RouteMap)
  - Register ObjectMapper with KeyFormatterWithoutConversion
- [x] Update DI config (`config/common/http.php`): bind SchemaMapper to HydratorMapper
- [x] Write unit tests for HydratorMapper:
  - Body params passed through
  - Path params renamed via paramMap
  - Auth params injected from request attributes
  - Query params included
  - All sources combined
- [x] Verify: `composer lp:run && composer ps:run && composer test:unit`
- [x] Run `composer test:web` -- E2E smoke tests pass

---

## Stage 4: Cleanup + Final Validation (~15min)

**Dependencies:** Stage 3

- [x] Delete `src/Presentation/Api/RouteMap.php`
- [x] Delete `src/Presentation/Api/MatchedOperation.php`
- [x] Delete `src/Infrastructure/Http/OpenApiSchemaMapper.php`
- [x] Remove old DI bindings for deleted classes
- [x] Delete related unit tests for removed classes (RouteMapCest, OpenApiSchemaMapperCest)
- [x] Update functional tests (ApiActionCest) for new API
- [x] Run `composer scan:all` (MANDATORY) -- all passed
- [x] Run full test suite -- all passed (188 unit, 52 integration, 11 functional, 5 web, 1 cli)

---

## Files Overview

| File | Action | Stage |
|------|--------|-------|
| `config/common/openapi/auth.php` | MODIFY | 1 |
| `config/common/openapi/plays.php` | MODIFY | 1 |
| `config/common/openapi/user.php` | MODIFY | 1 |
| `src/Presentation/Api/CompiledOperation.php` | CREATE | 2 |
| `src/Presentation/Api/CompiledRouteMap.php` | CREATE | 2 |
| `src/Presentation/Api/MatchResult.php` | CREATE | 2 |
| `src/Core/Http/SchemaMapper.php` | MODIFY | 3 |
| `src/Infrastructure/Http/HydratorMapper.php` | CREATE | 3 |
| `src/Presentation/Api/ApiAction.php` | MODIFY | 3 |
| `config/common/api-action.php` | MODIFY | 3 |
| `config/common/http.php` | MODIFY | 3 |
| `tests/Functional/ApiActionCest.php` | MODIFY | 4 |
| `src/Presentation/Api/RouteMap.php` | DELETE | 4 |
| `src/Presentation/Api/MatchedOperation.php` | DELETE | 4 |
| `src/Infrastructure/Http/OpenApiSchemaMapper.php` | DELETE | 4 |
| `tests/Unit/Presentation/Api/RouteMapCest.php` | DELETE | 4 |
| `tests/Unit/Infrastructure/Http/OpenApiSchemaMapperCest.php` | DELETE | 4 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Done | 2026-02-24 | Package installed, configs cleaned |
| 2 | Done | 2026-02-24 | CompiledRouteMap + tests |
| 3 | Done | 2026-02-24 | HydratorMapper + ApiAction refactored |
| 4 | Done | 2026-02-24 | Old classes deleted, all tests pass |
