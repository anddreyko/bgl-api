# Master Checklist: CORE-009 Compiled Route Map + Hydrator Mapper

> Task: bgl-aqk
> Created: 2026-02-23

## Overview

**Overall Progress:** 0 of 4 stages completed
**Current Stage:** Stage 1

---

## Stage 1: Install Package + Simplify OpenAPI Configs (~30min)

**Dependencies:** None

- [ ] Install `eventsauce/object-hydrator` via `docker compose run --rm api-php-cli composer require eventsauce/object-hydrator`
- [ ] Update `config/common/openapi/auth.php`:
  - Remove `x-target` and `x-source` from `sign-out` route body properties
  - Add `'x-auth' => ['userId']` at operation level for `sign-out`
- [ ] Update `config/common/openapi/plays.php`:
  - Remove `x-target` from `{id}` path parameter (CloseSession), add `'x-map' => ['id' => 'sessionId']` at operation level
  - Remove `x-target`/`x-source` from `userId` body properties
  - Remove `x-target` with `|datetime` cast from `finishedAt` (EventSauce handles from constructor type)
  - Add `'x-auth' => ['userId']` at operation level for both `post` (OpenSession) and `patch` (CloseSession)
- [ ] Update `config/common/openapi/user.php`:
  - Remove `x-target` from `{id}` path parameter, add `'x-map' => ['id' => 'userId']` at operation level
- [ ] Verify: `composer lp:run && composer ps:run`

---

## Stage 2: CompiledRouteMap + CompiledOperation (~40min)

**Dependencies:** Stage 1

- [ ] Create `src/Presentation/Api/CompiledOperation.php` -- readonly VO:
  - `string $messageClass` (class-string of Message)
  - `array $interceptors` (list of Interceptor class-strings)
  - `array $authParams` (from x-auth, e.g. `['userId']`)
  - `array $paramMap` (from x-map, e.g. `['id' => 'sessionId']`)
  - `array $openApiSchema` (raw OpenAPI operation for validation/export)
- [ ] Create `src/Presentation/Api/CompiledRouteMap.php`:
  - Constructor receives merged OpenAPI paths config
  - Boot-time compilation: split routes into static (no `{}`) and dynamic (with `{}`)
  - Static map: `'METHOD /path'` => CompiledOperation (HashMap, O(1))
  - Dynamic map: single combined regex with `(*MARK:key)` groups
  - `match(string $method, string $path): ?MatchResult` -- returns CompiledOperation + pathParams
- [ ] Create `src/Presentation/Api/MatchResult.php` -- readonly VO:
  - `CompiledOperation $operation`
  - `array $pathParams`
- [ ] Write unit tests for CompiledRouteMap:
  - Static route match (GET /v1/auth/sign-up)
  - Dynamic route match (GET /v1/user/{id})
  - No match returns null
  - Method mismatch returns null
  - Path params extracted correctly
- [ ] Verify: `composer lp:run && composer ps:run && composer test:unit`

---

## Stage 3: HydratorMapper + ApiAction Update (~40min)

**Dependencies:** Stage 2

- [ ] Update `src/Core/Http/SchemaMapper.php` interface:
  - Change signature to accept `CompiledOperation` instead of raw schema array
- [ ] Create `src/Infrastructure/Http/HydratorMapper.php`:
  - Collects body params, query params
  - Applies path param renames from `CompiledOperation::$paramMap`
  - Injects auth params from request attributes (`auth.{paramName}`)
  - Returns flat array for hydration
- [ ] Update `src/Presentation/Api/ApiAction.php`:
  - Replace `RouteMap` with `CompiledRouteMap`
  - Use `CompiledRouteMap::match()` returning `MatchResult`
  - Pass `MatchResult::$operation` to `SchemaMapper::map()`
  - Use `ObjectMapperUsingReflection::hydrate($messageClass, $data)` instead of `new $messageClass(...$data)`
- [ ] Update DI config to register CompiledRouteMap factory:
  - Load all OpenAPI configs, merge paths
  - Pass to CompiledRouteMap constructor
- [ ] Update DI config: bind SchemaMapper to HydratorMapper
- [ ] Write unit tests for HydratorMapper:
  - Body params passed through
  - Path params renamed via paramMap
  - Auth params injected from request attributes
  - Query params included
- [ ] Verify: `composer lp:run && composer ps:run && composer test:unit`
- [ ] Run `composer test:web` -- E2E smoke tests must pass

---

## Stage 4: Cleanup + Final Validation (~15min)

**Dependencies:** Stage 3

- [ ] Delete `src/Presentation/Api/RouteMap.php`
- [ ] Delete `src/Presentation/Api/MatchedOperation.php`
- [ ] Delete `src/Infrastructure/Http/OpenApiSchemaMapper.php`
- [ ] Remove old DI bindings for deleted classes
- [ ] Delete or update related unit tests for removed classes
- [ ] Run `composer scan:all` (MANDATORY)
- [ ] Run `composer dt:run` (deptrac architecture check)
- [ ] Run `composer test:all` (full test suite)

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
| DI config (http.php or similar) | MODIFY | 3 |
| `src/Presentation/Api/RouteMap.php` | DELETE | 4 |
| `src/Presentation/Api/MatchedOperation.php` | DELETE | 4 |
| `src/Infrastructure/Http/OpenApiSchemaMapper.php` | DELETE | 4 |

---

## Progress Log

| Stage | Status | Completed | Notes |
|-------|--------|-----------|-------|
| 1 | Not Started | - | |
| 2 | Not Started | - | |
| 3 | Not Started | - | |
| 4 | Not Started | - | |
