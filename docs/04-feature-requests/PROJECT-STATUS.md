# Project Status

> Updated: 2025-12-29

## Current Focus

**CORE-004: API Response Contracts**

Folder: `docs/04-feature-requests/0002-core-004-api-response-contracts/`

Implement standardized API response format (SuccessResponse, ErrorResponse) for all endpoints. Empty classes exist, need
to add actual contracts.

## Blockers

No critical blockers.

## Next Tasks

1. **CORE-004** — API Response Contracts (newly added)
2. **AUTH-001** — Registration (Handler + Action + tests)
3. **AUTH-003** — Refresh Token (entity + handler)
4. **AUTH-004** — JWT Auth Middleware
5. **GAMES-001** — Game search via BGG

## Completed

- Infrastructure: Docker, Makefile, vendor-bin (partial INFRA-001)
- Core layer: Messages, ValueObjects, Collections, Listing
- Tactician MessageBus with middleware pipeline
- Doctrine and InMemory repositories
- AUTH-002: LoginByCredentials handler (partial)
- Basic tests: Unit, Integration, Functional
- Searchable contract for Doctrine repository (internal)

## MVP Progress

**Status:** In Progress
**Phase 0:** 0/4 tasks (0%)
**Phase 1:** 0/17 tasks (0%)
**Overall MVP:** ░░░░░░░░░░ ~15% (infrastructure work done, not formally tracked)

## Metrics

| Metric                    | Current | Target |
|---------------------------|---------|--------|
| Integration test coverage | ~60%    | 80%    |
| Psalm level               | 1       | 1      |
| Deptrac violations        | 0       | 0      |
| `make scan`               | Pass    | Pass   |

---

> For task details and acceptance criteria see [BACKLOG.md](BACKLOG.md)
