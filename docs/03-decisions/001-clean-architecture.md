# Clean Architecture

## Date: 2024-01-15

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

When designing BoardGameLog, we needed to choose an architectural approach that would ensure long-term maintainability,
testability, and independence from specific technical solutions.

**Requirements:**

- Business logic independence from frameworks and databases
- High testability of all components
- Ability to replace infrastructure components without changing business logic
- Clear boundaries between system layers
- Understandable structure for the development team

**Context:**

- The project is planned for long-term development
- Technology changes are possible (e.g., switching to a different database)
- Support for different interface types is important (API, CLI, future frontend applications)

### Considered Options

#### Option 1: Monolithic Framework-Based Architecture

Traditional approach using a framework (Laravel, Symfony) as the architectural foundation.

**Pros:**

- Fast development start
- Many ready-made components
- Large community and documentation

**Cons:**

- High coupling with the framework
- Difficulty testing business logic in isolation
- Framework updates may require significant refactoring
- Business logic spreads across controllers and models

#### Option 2: Clean Architecture (Hexagonal / Ports & Adapters)

Architecture with concentric layers and inward-directed dependencies.

**Pros:**

- Business logic is completely independent of infrastructure
- Easy to test each layer in isolation
- Any infrastructure component can be replaced
- Clear separation of responsibilities
- Suitable for long-term development

**Cons:**

- More boilerplate code
- Steeper learning curve for new developers
- Requires discipline in maintaining layer boundaries
- Slower start compared to framework-oriented approach

#### Option 3: Microservices Architecture

Distributed architecture with independent services.

**Pros:**

- Independent service scaling
- Technological flexibility for each service
- Failure isolation

**Cons:**

- Excessive complexity for MVP
- Requires developed infrastructure (orchestration, monitoring)
- Network latency between services
- Distributed transactions

### Decision

**Decision:** Clean Architecture adopted (Option 2)

**Reason for choice:**

1. **Long-term maintainability** — the project is planned for years of development, resilience to technology changes is
   important
2. **Testability** — critical for product quality, Clean Architecture allows isolated business logic testing
3. **Flexibility** — ability to use different DB adapters (InMemory for tests, Doctrine for production)
4. **Team scalability** — clear layer boundaries simplify parallel work by multiple developers

### Consequences

**Positive:**

- Business logic is protected from infrastructure changes
- Ability to use InMemory repositories for fast tests
- Easy component replacement (e.g., switching from Doctrine to another ORM)
- Clear project structure for new team members

**Negative/Risks:**

- Increased code volume (interfaces, adapters)
- Need to strictly monitor dependency direction
- Team training time required
- Risk of over-engineering for simple features

### Notes

**Layer Structure:**

```
src/
├── Core/           # Contracts, interfaces, base VOs
├── Domain/         # Business logic, entities, rules
├── Application/    # Use cases, handlers
├── Infrastructure/ # DB, external services
└── Presentation/   # API, CLI
```

**Dependency Rule:** Each layer can only depend on layers that are inside (Core, Domain). Outer layers (Infrastructure,
Presentation) depend on inner layers, but not vice versa.

**Enforcement:** Dependency rules are automatically verified via `composer dt` (Deptrac).

**References:**

- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)
