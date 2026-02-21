# Project Status

> Updated: 2026-01-05

## Current Focus

**CORE-007: Input Validation (ADR-012)**

Next task after CORE-003 completion.

See: `docs/04-feature-requests/0006-core-007-input-validation/`

## Blockers

No critical blockers.

## Next Tasks

1. **CORE-007** — Input Validation (ADR-012) **[NEXT]**
2. **CORE-002** — Password Hashing Contract and Component
3. **CORE-008** — Token Generator Contract and Component
4. **AUTH-001** — Registration (Handler + Action + tests)

## Completed

- Infrastructure: Docker, Makefile, vendor-bin (partial INFRA-001)
- Core layer: Messages, ValueObjects, Collections, Listing
- Tactician MessageBus with middleware pipeline
- Doctrine and InMemory repositories
- AUTH-002: LoginByCredentials handler (partial)
- Basic tests: Unit, Integration, Functional
- Searchable contract for Doctrine repository (internal)
- **CORE-004: API Response Contracts** — SuccessResponse, ErrorResponse with pagination and validation errors
- **CORE-005: OAuth Server Contract** — Authentificator contract, Identity value object, GrantType enum,
  LeagueAuthServer adapter
- **CORE-001: Denormalization and Serialization Components** — Denormalizer/Serializer contracts,
  SauceDenormalizer (EventSauce), FractalSerializer (League Fractal), TransformerRegistry, DI config
- **CORE-003: Mediator Pattern** — Unified ApiAction entry point, RouteMessageMap, InterceptorPipeline,
  Transactional aspect, DenormalizationInterceptor, Auth/Validation placeholders, /ping endpoint

## MVP Progress

**Status:** In Progress
**Phase 0:** 5/8 tasks (62.5%)
**Phase 1:** 0/17 tasks (0%)
**Overall MVP:** 25% (CORE-004, CORE-005, CORE-001, CORE-003 completed)

## Metrics

| Metric                    | Current | Target |
|---------------------------|---------|--------|
| Integration test coverage | ~60%    | 80%    |
| Psalm level               | 1       | 1      |
| Deptrac violations        | 0       | 0      |
| `composer scan:all`       | Pass    | Pass   |

---

> For task details and acceptance criteria see [BACKLOG.md](BACKLOG.md)
