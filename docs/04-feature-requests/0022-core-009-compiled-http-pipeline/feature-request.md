# Feature Request: CORE-009 Compiled Route Map + Hydrator Mapper

**Task:** bgl-aqk
**Date:** 2026-02-23
**Status:** Open
**Priority:** P2

---

## 1. Feature Overview

### Description

Refactor HTTP-to-Message pipeline: replace RouteMap (O(N) regex per request) with CompiledRouteMap (O(1) HashMap for static routes + single compiled regex for dynamic routes). Replace OpenApiSchemaMapper (148 lines of manual reflection mapping) with HydratorMapper using `eventsauce/object-hydrator`. Simplify OpenAPI config: remove `x-target`/`x-source` string hacks, introduce `x-map`/`x-auth` at operation level.

### Business Value

- O(1) route matching instead of O(N) regex per request
- ~180 lines of custom code replaced by battle-tested library (EventSauce Object Hydrator)
- Clean OpenAPI configs without encoding hacks (`x-target => 'finishedAt|datetime'`)
- Automatic snake_case->camelCase + type casting from constructor types (DateTimeImmutable, etc.)

### Motivation

Current pipeline has 3 problems:
1. **O(N*M) route matching** -- RouteMap iterates all paths with regex on every request
2. **x-target/x-source encoding** -- field name, rename target, and type cast packed into one string
3. **Manual mapping with reflection** -- OpenApiSchemaMapper (148 lines) does what EventSauce does automatically

---

## 2. Technical Architecture

### Approach

**Phase 1: Install package + simplify configs**
- Install `eventsauce/object-hydrator` via composer
- Replace `x-target`/`x-source` with `x-map` (explicit renames) and `x-auth` (auth-injected params) at operation level
- Automatic mapping by convention: `finished_at` -> `finishedAt` (EventSauce), direct name match, type cast from constructor

**Phase 2: CompiledRouteMap**
- Boot-time compilation of OpenAPI configs
- Static map: `"GET /v1/ping"` -> CompiledOperation (HashMap, O(1))
- Dynamic map: single combined regex with MARK groups for parameterized routes
- CompiledOperation holds: messageClass, interceptors, authParams, paramMap, openApiSchema

**Phase 3: HydratorMapper**
- Replace OpenApiSchemaMapper with ~30-line HydratorMapper
- Collects body + query + path params (with x-map renames) + auth attributes
- ApiAction uses `ObjectMapperUsingReflection::hydrate()` instead of `new $messageClass(...$data)`

**Phase 4: Cleanup**
- Delete RouteMap, MatchedOperation, OpenApiSchemaMapper
- Update DI configs

### Integration Points

- `config/common/openapi/*.php` -- config format changes
- `src/Presentation/Api/ApiAction.php` -- uses CompiledRouteMap + hydrator
- `src/Core/Http/SchemaMapper.php` -- interface updated
- DI container config

### Packages

- `eventsauce/object-hydrator` (ADR-010) -- zero-reflection-at-runtime hydration

---

## 3. Directory Structure

```
src/Presentation/Api/
    CompiledRouteMap.php        # O(1) static + compiled regex dynamic routing
    CompiledOperation.php       # Immutable VO: messageClass, interceptors, authParams, paramMap, schema

src/Infrastructure/Http/
    HydratorMapper.php          # EventSauce-based mapper (replaces OpenApiSchemaMapper)

config/common/openapi/
    auth.php                    # Updated: x-map/x-auth instead of x-target/x-source
    plays.php                   # Updated
    user.php                    # Updated
```

### Files to delete

```
src/Presentation/Api/RouteMap.php
src/Presentation/Api/MatchedOperation.php
src/Infrastructure/Http/OpenApiSchemaMapper.php
```

---

## 4. Testing Strategy

### Unit Tests

- CompiledRouteMap: static match, dynamic match, no match, method mismatch
- HydratorMapper: body params, query params, path params with x-map, auth params injection
- Integration: full pipeline ApiAction with CompiledRouteMap + HydratorMapper

### Existing Tests

- E2E tests in `tests/Web/` cover full HTTP flow -- must stay green
- Functional tests for handlers -- unaffected (transport-agnostic)

---

## 5. Acceptance Criteria

- [ ] `eventsauce/object-hydrator` installed
- [ ] OpenAPI configs use `x-map`/`x-auth` instead of `x-target`/`x-source`
- [ ] CompiledRouteMap with O(1) static + compiled regex dynamic routing
- [ ] HydratorMapper uses EventSauce for array->Command hydration
- [ ] ApiAction updated to use new pipeline
- [ ] RouteMap, MatchedOperation, OpenApiSchemaMapper deleted
- [ ] `composer scan:all` passes
- [ ] `composer test:web` passes (E2E smoke tests)
- [ ] `composer dt:run` passes (deptrac)
