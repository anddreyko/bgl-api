# Master Checklist: CORE-003 Mediator Pattern (Remaining Work)

**Task:** bgl-ebc
**Size:** Large
**Approach:** TDD

---

## Context

ApiAction, RouteMap, InterceptorPipeline, SchemaMapper, MatchedOperation are ALREADY IMPLEMENTED
(commit b2917a3). This task covers the remaining pieces:
1. Transactional aspect
2. Tests for all new components
3. Quality gates

---

## Stage 1: Transactional Aspect -- Tests (Red)

- [ ] Create `tests/Functional/TransactionalAspectCest.php`
  - Test: handler result returned on success, flush + commit called
  - Test: exception causes rollback, exception re-thrown
  - Test: aspect wraps handler execution in transaction
- [ ] Run tests -- confirm they FAIL (Red)

## Stage 2: Transactional Aspect -- Implementation (Green)

- [ ] Create `src/Application/Aspects/Transactional.php`
  - Implements `MessageMiddleware`
  - Inject `EntityManagerInterface`
  - `beginTransaction()` -> handler -> `flush()` -> `commit()`
  - On exception: `rollback()` -> re-throw
- [ ] Register in `config/common/bus.php` middleware array (after Logging)
- [ ] Run tests -- confirm they PASS (Green)

## Stage 3: Tests for ApiAction Flow

> Note: RouteMap and OpenApiSchemaMapper were replaced by CompiledRouteMap and HydratorMapper in CORE-009 (FR-0022).
> Their tests already exist. Only ApiAction functional test remains.

- [ ] Create `tests/Functional/ApiAction/ApiActionCest.php` (Application layer -> Functional suite per ADR-015)
  - Test: successful dispatch returns JSON response
  - Test: route not found returns 404
  - Test: method not allowed returns 405
  - Test: handler exception returns error response
- [ ] Run all tests -- confirm they PASS

## Stage 4: Quality Gates

- [ ] Run `composer lp:run` -- passes
- [ ] Run `composer ps:run` -- passes
- [ ] Run `composer dt:run` -- passes
- [ ] Run `composer test:unit` -- passes
- [ ] Run `composer test:func` -- passes

## Validation Criteria

- Transactional aspect follows same pattern as Logging aspect
- EntityManager transaction lifecycle: begin -> handler -> flush -> commit (or rollback)
- All new Presentation classes (ApiAction, RouteMap, InterceptorPipeline) have test coverage
- Middleware order in bus.php: Logging -> Transactional
- No Psalm suppressions
- All scan passes
