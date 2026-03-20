# Feature Request: Schema-Based Request Mapping (CORE-001 Part 2)

**Date:** 2026-02-22
**Status:** In Progress
**Priority:** P0 (Foundation)

---

## 1. Overview

Inbound request mapping layer:
HTTP request data -> Command/Query objects -> Dispatcher -> Handler -> response -> serialized HTTP response.

This is the remaining part of CORE-001. Part 1 (outbound serialization via FractalSerializer) was completed in FR-0007.

### Problem

Currently there is no unified entry point for API requests. Each route would need its own controller action. The project needs:
- A single `ApiAction` that handles all API routes
- Route-to-message mapping via OpenAPI config with `x-message` extensions (ADR-011)
- Request data extraction (body, query, path params) and mapping to Command constructor args
- Interceptor pipeline for cross-cutting concerns (auth, rate limiting)

### Scope

- `ApiAction` -- single PSR-15 handler for all API routes
- `RouteMessageMap` -- matches HTTP method + path to message class using OpenAPI config
- `InterceptorPipeline` -- executes interceptor chain before dispatch
- `SchemaRequestMapper` interface (Core) + implementation (Infrastructure)
- OpenAPI config structure with x-message, x-interceptors, x-target extensions

### Out of Scope

- Specific interceptor implementations (auth, rate limiting) -- separate tasks
- OpenAPI response validation -- separate task (OPENAPI-VAL)
- Outbound serialization -- already done (FR-0007)

## 2. Technical Context

### Existing Code

- `src/Core/Messages/Message.php`, `Command.php` -- message interfaces
- `src/Core/Messages/Dispatcher.php` -- message bus interface
- `src/Core/Serialization/Serializer.php` -- output serialization
- `src/Presentation/Api/V1/Responses/` -- ErrorResponse, SuccessResponse
- `config/common/openapi/` -- OpenAPI config files (ping.php, v1.php)
- `web/index.php` -- already imports `ApiAction` (class missing)

### ADRs

- ADR-011: Unified Route Configuration (x-message, x-interceptors, x-target)
- ADR-010: Serialization and Hydration
- ADR-003: Mediator Pattern

## 3. Dependencies

- None (foundation task)

## 4. Acceptance Criteria

- [ ] ApiAction handles incoming requests, matches route, creates messages, dispatches, serializes result
- [ ] RouteMap reads OpenAPI config and matches HTTP method + path
- [ ] Path parameters extracted correctly (e.g. /v1/auth/email/verify)
- [ ] InterceptorPipeline runs interceptors from config before dispatch
- [ ] SchemaMapper maps request body/query/path data to named array for messages constructor
- [ ] Domain exceptions (DuplicateEmailException, InvalidTokenException) mapped to appropriate HTTP status codes
- [ ] Invalidated schemas return 422
- [ ] Unknown routes return 404
- [ ] Denied routes return 403
- [ ] All files pass `composer scan:all`
- [ ] Unit tests for RouteMap, InterceptorPipeline, SchemaMapper
