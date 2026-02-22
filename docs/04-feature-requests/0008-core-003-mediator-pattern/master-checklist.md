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

- [ ] Create `tests/Functional/ApiAction/ApiActionCest.php` (or similar)
  - Test: successful dispatch returns JSON response
  - Test: route not found returns 404
  - Test: method not allowed returns 405
  - Test: handler exception returns error response
- [ ] Create `tests/Unit/Presentation/RouteMapCest.php`
  - Test: path matching with parameters
  - Test: method matching
  - Test: no match returns null
  - Test: extracts x-message, x-interceptors, schema
- [ ] Create `tests/Unit/Presentation/InterceptorPipelineCest.php`
  - Test: empty pipeline returns request unchanged
  - Test: interceptor modifies request
  - Test: multiple interceptors chain correctly
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
