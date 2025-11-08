# Migration to Async PHP Runtime

## Date: 2024-01-20

## Authors: BoardGameLog Team

## Status: Proposed

---

### Context

As the project grows and load increases, the traditional PHP model (one process per request) becomes a bottleneck. For
Phase 3 (Scaling), migration to async runtime is planned to improve performance and resource efficiency.

**Current State:**

The project uses the classic synchronous PHP model with Slim Framework. Each HTTP request is processed by a separate
process, leading to initialization overhead and limiting concurrent processing capabilities.

**Challenges:**

- High bootstrap overhead on each request
- Inefficient resource usage during I/O-bound operations
- Limited concurrency when working with external APIs
- Scaling requires increasing process count

**Requirements:**

- Maintain compatibility with existing business logic
- Minimal changes to Domain and Application layers
- Support for long-lived connections (WebSocket for notifications)
- Efficient work with external APIs (BGG, push services)

---

### Considered Options

#### Option 1: Stay on Synchronous PHP

Scale through increasing PHP-FPM workers and horizontal server scaling.

**Pros:**

- No migration costs
- Simple execution model
- Wide library support

**Cons:**

- High overhead per request
- Inefficient memory usage
- Difficulties with real-time functionality

#### Option 2: Async PHP Runtime

Migration to async runtime with event loop and non-blocking I/O.

**Pros:**

- Single process handles many requests
- Efficient resource usage during I/O operations
- Native WebSocket and long-polling support
- Concurrent external API requests

**Cons:**

- Requires infrastructure layer adaptation
- Not all libraries are compatible
- More complex execution model
- Need to manage state between requests

#### Option 3: Hybrid Approach

Async runtime for specific tasks (WebSocket, background jobs), synchronous PHP for main API.

**Pros:**

- Gradual migration
- Lower risk
- Async only where it provides benefit

**Cons:**

- Two runtimes in production
- Infrastructure complexity
- Some component duplication

---

### Decision

**Decision:** Planned migration to async PHP runtime (Option 2) at Phase 3.

**Reason for choice:**

1. **Architectural readiness** — Clean Architecture and Ports & Adapters allow replacing infrastructure layer without
   changing business logic
2. **Phase 3 requirements** — real-time notifications, social feed, and public API require efficient handling of many
   connections
3. **Resource savings** — single async process replaces dozens of PHP-FPM workers
4. **Concurrency** — parallel external API requests without blocking

---

### Consequences

**Positive:**

- Significant reduction in memory and CPU consumption
- Native WebSocket support for notifications
- Efficient external API work
- Ability to handle thousands of connections with single process

**Negative/Risks:**

- Need to rewrite Infrastructure layer
- Replace some libraries with async-compatible alternatives
- More complex concurrent code debugging
- Memory leak risk in long-lived processes

**Mitigation:**

- Domain and Application layers remain unchanged thanks to Clean Architecture
- Thorough testing before migration
- Gradual rollout with canary deployment
- Memory and performance monitoring

---

### Notes

**Migration Preparation:**

Current architecture is already prepared for transition. Key principles that simplify migration:

1. **Ports & Adapters** — all external dependencies are isolated in Infrastructure, only adapters need replacement
2. **Stateless Handlers** — handlers don't store state between calls
3. **Immutable Value Objects** — no mutable state reduces race condition risk
4. **Repository Pattern** — storage abstraction allows DB driver replacement

**Components Requiring Adaptation:**

| Component   | Current State  | Required Changes               |
|-------------|----------------|--------------------------------|
| HTTP Layer  | Slim Framework | Replace with async HTTP server |
| Database    | Doctrine ORM   | Async database driver          |
| HTTP Client | Guzzle         | Async HTTP client              |
| Cache       | Redis (sync)   | Async Redis client             |

**Components Without Changes:**

- Core Layer (contracts, Value Objects)
- Domain Layer (entities, business rules)
- Application Layer (Handlers, Messages)

**Timeline:**

Migration is scheduled for Phase 3 after Phase 2 functionality stabilization. Exact timeline will be determined based on
load testing results.

**References:**

- [Async PHP Ecosystem](https://amphp.org/)
- [ReactPHP](https://reactphp.org/)
- [Swoole](https://www.swoole.co.uk/)
