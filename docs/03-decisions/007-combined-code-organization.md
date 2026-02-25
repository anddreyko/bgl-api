# Combined Code Organization Approach

## Date: 2024-01-20

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

When designing the project structure, two main approaches to code organization were considered: classic layered
architecture (Layered Architecture) and feature-focused organization (Vertical Slices). Each approach has its advantages
but also requires different maintenance costs.

**Challenges:**

- Limited resources during MVP phase
- Small development team
- Need for quick start
- Desire to preserve scaling capability

**Requirements:**

- Minimize time spent on boilerplate structure creation
- Clear code navigation
- Related functionality grouping
- Compatibility with Clean Architecture

---

### Considered Options

#### Option 1: Full Feature-Focused Approach (Vertical Slices)

Each feature is a fully autonomous module with all layers inside.

```
src/
└── Feature/
    ├── CreatePlay/
    │   ├── Domain/
    │   │   └── Play.php
    │   ├── Application/
    │   │   └── Handler.php
    │   ├── Infrastructure/
    │   │   └── DoctrinePlayRepository.php
    │   └── Presentation/
    │       └── CreatePlayAction.php
    └── GetPlayHistory/
        ├── Domain/
        ├── Application/
        ├── Infrastructure/
        └── Presentation/
```

**Pros:**

- Maximum feature isolation
- Easy to remove or replace entire feature
- Suitable for microservice decomposition
- Minimal dependencies between features

**Cons:**

- Lots of structure duplication
- Difficulty reusing entities between features
- High overhead for creating each feature
- Excessive for small project

#### Option 2: Classic Layered Architecture

Code organized strictly by technical layers.

```
src/
├── Domain/
│   └── Entities/
│       ├── Play.php
│       ├── Game.php
│       └── User.php
├── Application/
│   └── Handlers/
│       ├── CreatePlayHandler.php
│       └── GetPlayHistoryHandler.php
├── Infrastructure/
│   └── Repositories/
└── Presentation/
    └── Actions/
```

**Pros:**

- Simple structure
- Understandable for newcomers
- Minimum boilerplate

**Cons:**

- Related code scattered across different directories
- Hard to understand feature boundaries
- Layers become overloaded as project grows

#### Option 3: Combined Approach

Layered architecture with domain grouping within layers.

```
src/
├── Domain/
│   ├── Auth/
│   │   ├── Entities/
│   │   ├── Services/
│   │   └── Repositories/
│   ├── Plays/
│   │   ├── Entities/
│   │   ├── Events/
│   │   └── Repositories/
│   └── Games/
├── Application/
│   └── Handlers/
│       ├── Auth/
│       │   ├── IssueToken/
│       │   └── RevokeToken/
│       └── Plays/
│           ├── CreatePlay/
│           └── GetPlayHistory/
├── Infrastructure/
│   └── Persistence/
│       └── Doctrine/
└── Presentation/
    └── Api/
```

**Pros:**

- Balance between isolation and simplicity
- Features grouped by domain areas
- Entity reuse within domain
- Less boilerplate than full feature-focused
- Clear context boundaries

**Cons:**

- Less strict isolation than vertical slices
- Requires discipline in respecting domain boundaries

---

### Decision

**Decision:** Combined approach adopted (Option 3)

Features are conditionally extracted within the domain layer by grouping by business contexts (Auth, Plays, Games,
Stats). Within each context, code is sliced by use cases in the Application Layer.

**Reason for choice:**

1. **Time savings** — no need to create full vertical structure for each feature
2. **Reuse** — domain entities naturally shared within context
3. **Clarity** — developer immediately sees which domain code belongs to
4. **Scalability** — domain can be extracted into separate module/service if needed
5. **Compatibility** — fully compliant with Clean Architecture

---

### Consequences

**Positive:**

- Quick development start without excessive boilerplate
- Code logically grouped by business areas
- Easy to find all parts of functionality
- Simple entity and service reuse within domain

**Negative/Risks:**

- Domain boundaries may blur without discipline
- Less isolation than with full vertical slices
- May require refactoring to vertical slices with significant growth

---

### Notes

#### Project Structure

```
src/
├── Core/                               # Shared contracts and VOs
│   ├── Collections/
│   ├── Listing/
│   ├── Messages/
│   └── ValueObjects/
│
├── Domain/                             # Business logic grouped by context
│   ├── Auth/                           # Authentication context
│   │   ├── Entities/
│   │   │   └── User.php
│   │   ├── Services/
│   │   ├── Repositories/
│   │   │   └── Users.php               # Interface
│   │   └── ValueObjects/
│   │
│   ├── Games/                          # Games context
│   │   ├── Entities/
│   │   │   └── Game.php
│   │   └── Repositories/
│   │
│   ├── Plays/                          # Sessions context
│   │   ├── Entities/
│   │   │   ├── Play.php
│   │   │   └── Player.php
│   │   ├── Events/
│   │   │   └── PlayCreated.php
│   │   └── Repositories/
│   │
│   └── Stats/                          # Statistics context
│       └── Services/
│
├── Application/                        # Use cases sliced by features
│   ├── Aspects/
│   └── Handlers/
│       ├── Auth/
│       │   ├── IssueToken/
│       │   │   ├── Command.php
│       │   │   └── Handler.php
│       │   └── RevokeToken/
│       │       ├── Command.php
│       │       └── Handler.php
│       ├── Plays/
│       │   ├── CreatePlay/
│       │   │   ├── Command.php
│       │   │   └── Handler.php
│       │   └── GetPlayHistory/
│       │       ├── Query.php
│       │       └── Handler.php
│       └── Games/
│           └── SearchGames/
│               ├── Query.php
│               └── Handler.php
│
├── Infrastructure/                     # Implementations grouped by technology
│   ├── Persistence/
│   │   ├── Doctrine/
│   │   │   ├── Users.php               # Repository implementation
│   │   │   └── Plays.php
│   │   └── InMemory/
│   └── MessageBus/
│       └── Tactician/
│
└── Presentation/                       # Entry points
    ├── Api/
    │   └── Actions/
    │       ├── Auth/
    │       └── Plays/
    └── Console/
```

#### Grouping Rules

| Layer          | Grouping                   | Example                          |
|----------------|----------------------------|----------------------------------|
| Domain         | By business context        | `Domain/Plays/`, `Domain/Games/` |
| Application    | By use case within context | `Handlers/Plays/CreatePlay/`     |
| Infrastructure | By technology              | `Persistence/Doctrine/`          |
| Presentation   | By interface and context   | `Api/Actions/Plays/`             |

#### When to Create New Context

A new domain context is created when:

- A clear business area appears with its own entities
- Entities have their own lifecycle
- There are clear responsibility boundaries

#### Migration to Vertical Slices

If needed (team growth, microservice transition), a domain can be extracted into a full vertical slice:

```
# Before (combined)
src/Domain/Plays/
src/Application/Handlers/Plays/
src/Infrastructure/Persistence/Doctrine/Plays.php
src/Presentation/Api/Actions/Plays/

# After (vertical slice)
src/Plays/
├── Domain/
├── Application/
├── Infrastructure/
└── Presentation/
```

#### Relationship to Bounded Contexts

Domain groupings correspond to Bounded Contexts from DDD:

| Directory       | Bounded Context | Responsibility                              |
|-----------------|-----------------|---------------------------------------------|
| `Domain/Profile/` | Profile Context | User identity, profile, settings          |
| `Domain/Games/` | Games Context   | Game catalog                                |
| `Domain/Plays/` | Plays Context   | Session logging                             |
| `Domain/Stats/` | Stats Context   | Analytics, reports                          |
| `Domain/Sync/`  | Sync Context    | External source synchronization abstraction |

**Ports & Adapters for External Integrations:**

Sync Context defines interfaces (ports) for working with external data sources. Specific implementations (adapters) are
in Infrastructure:

```
Domain/Sync/
├── GameCatalogProvider.php        # Game search interface
├── PlayExporter.php               # Session export interface
├── PlayImporter.php               # Session import interface
└── Services/
    └── SyncService.php            # Domain sync service

Infrastructure/Sync/
└── Bgg/                           # BoardGameGeek adapter
    ├── BggCatalogProvider.php
    ├── BggPlayExporter.php
    └── BggPlayImporter.php
```

This allows adding other sources (e.g., alternative game catalogs) without changing domain logic.

**References:**

- [Vertical Slice Architecture](https://jimmybogard.com/vertical-slice-architecture/)
- [Feature Folders](https://docs.microsoft.com/en-us/archive/msdn-magazine/2016/september/asp-net-core-feature-slices-for-asp-net-core-mvc)
- [Screaming Architecture by Uncle Bob](https://blog.cleancoder.com/uncle-bob/2011/09/30/Screaming-Architecture.html)
