# AGENTS.md – BoardGameLog API

## 1. Project Context

**BoardGameLog (BGL)** — API platform for tracking board game sessions.

**Core Stack:** PHP 8.4, PostgreSQL, Slim 4, Doctrine ORM, Codeception

**Architecture:** Clean Architecture, DDD, CQS, Ports & Adapters, Mediator Pattern, Middleware-based AOP, SOLID
Principles

**Always operate from the project root directory.**

---

## 2. Commands (ONLY via `make`)

**CRITICAL: Never run tools directly. All commands via `make` only.**

### Code Quality

| Command     | Purpose                                           |
|-------------|---------------------------------------------------|
| `make cs`   | Fix code style (PHP-CS-Fixer)                     |
| `make rc`   | Apply automated refactoring (Rector)              |
| `make lp`   | PHP syntax check (Lint)                           |
| `make ps`   | Static type analysis (Psalm)                      |
| `make dt`   | Architecture dependency check (Deptrac) — **LAW** |
| `make cd`   | Check composer dependencies                       |
| `make scan` | **MANDATORY before push.** Full validation.       |

### Testing

| Command       | Purpose                  |
|---------------|--------------------------|
| `make t-all`  | Run all test suites      |
| `make t-unit` | Unit tests               |
| `make t-func` | Functional tests         |
| `make t-intg` | Integration tests        |
| `make t-web`  | Acceptance API tests     |
| `make t-cli`  | Acceptance CLI tests     |
| `make t-cov`  | Generate coverage report |
| `make in`     | Mutation testing         |

### Environment

| Command     | Purpose           |
|-------------|-------------------|
| `make init` | Initialize Docker |
| `make up`   | Start containers  |
| `make down` | Stop containers   |

---

## 3. Project Structure

```
src/
├── Core/                    # Contracts, interfaces, shared Value Objects
│   ├── Collections/
│   ├── Listing/
│   ├── Messages/            # Message, Command, Query, Event, MessageBus
│   └── ValueObjects/
│
├── Domain/                  # Business logic by Bounded Context
│   ├── Auth/                # Authentication & authorization
│   ├── Games/               # Game catalog
│   ├── Plays/               # Session logging
│   ├── Stats/               # Analytics & statistics
│   └── Sync/                # External integration ports
│
├── Application/             # Use cases
│   ├── Aspects/             # Middleware (Logging, Transactional, etc.)
│   └── Handlers/            # One handler = one use case
│       └── {Context}/{UseCase}/
│           ├── Command.php  # or Query.php
│           └── Handler.php
│
├── Infrastructure/          # External services & adapters
│   ├── Persistence/
│   │   ├── Doctrine/        # Repository implementations
│   │   └── InMemory/        # For tests
│   ├── MessageBus/Tactician/
│   └── Clients/Bgg/         # BoardGameGeek adapter
│
└── Presentation/            # Entry points
    ├── Api/                 # HTTP API
    └── Console/             # CLI commands
```

---

## 4. Architectural Dependency Law

**Inner layers never depend on outer layers. Enforced by `make dt`.**

```
Infrastructure → Application → Domain → Core
Presentation  ↗
```

**Dependency direction:** Outer layers depend on inner layers, never reverse.

---

## 5. Bounded Contexts

| Context | Responsibility                        | Location        |
|---------|---------------------------------------|-----------------|
| Auth    | Authentication, authorization, users  | `Domain/Auth/`  |
| Games   | Game catalog management               | `Domain/Games/` |
| Plays   | Session logging and management        | `Domain/Plays/` |
| Stats   | Analytics and reporting               | `Domain/Stats/` |
| Sync    | External integration (contracts only) | `Domain/Sync/`  |

**Sync Context** defines interfaces (ports). Adapters live in `Infrastructure/Sync/`.

---

## 6. Key Patterns

### Ports & Adapters

Domain defines interfaces, Infrastructure implements:
`Domain/Sync/GameCatalogProvider` → `Infrastructure/Sync/Bgg/BggCatalogProvider`

### Aspects (Middleware)

Configured in DI container as middleware, NOT as attributes:

```php
$commandBus = new CommandBus([
    new TacticianWrapMiddleware(Logging::class, $container),
    new TacticianWrapMiddleware(Transactional::class, $container),
    new CommandHandlerMiddleware(...),
]);
```

### Domain Events

Processed within transactions but NOT stored (no Event Sourcing in MVP). See ADR-006.

### Testing Trophy

Priority: Static Analysis → Integration → Unit → E2E. Integration tests = main confidence source.

---

## 7. Code Rules

### General

- PSR-12 strictly
- `declare(strict_types=1);` in every file
- `PascalCase` classes, `camelCase` methods/variables, `UPPER_SNAKE_CASE` constants

### By Layer

| Layer         | Location                                       | Rules                                                    |
|---------------|------------------------------------------------|----------------------------------------------------------|
| Core          | `Core`                                         | Non-domain contracts                                     |
| Value Objects | `Core/ValueObjects/`, `Domain/*/ValueObjects/` | Immutable, validate in constructor, return new instances |
| Entities      | `Domain/*/Entities/`                           | Rich objects, private props, no deps except Enums        |
| Repositories  | Contracts: Domain, Adapters: Infrastructure    | Return Entities/VOs/scalars, no business logic           |
| Handlers      | `Application/Handlers/`                        | One handler = one use case, coordinate Domain + Infra    |
| Presentation  | `Presentation/Web/`, `Presentation/Cli/`       | Thin: input → Message → MessageBus → response            |

---

## 8. Workflow

**See full workflow guide:** `docs/02-onboarding/05-workflow.md`

### Quick Reference

| Stage         | Command              | Required      |
|---------------|----------------------|---------------|
| Before commit | `make lp`, `make ps` | Yes           |
| Before push   | `make scan`          | **MANDATORY** |

### Testing Order (Trophy)

1. Static Analysis: `make lp`, `make ps`, `make dt`
2. Functional & Integration Tests: `make t-func`, `make t-intg` — **main focus**
3. Unit Tests: `make t-unit` — complex logic only
4. Acceptance Tests: `make t-web`, `make t-cli`
5. Mutation Testing: `make in`

### TDD Rules

| When                                | Approach          |
|-------------------------------------|-------------------|
| New functionality OR tests exist    | Use TDD           |
| Bug fix / refactoring without tests | Write tests after |

### Test Placement

| Type        | Location              | Requirements                 |
|-------------|-----------------------|------------------------------|
| Unit        | `tests/Unit/{path}/`  | No dependencies              |
| Functional  | `tests/Functional/`   | Mock externals, fixtures     |
| Integration | `tests/Integration/`  | DB, fixtures, mock externals |
| CLI         | `tests/Cli/*.feature` | DB, fixtures, mock externals |
| Web         | `tests/Web/*.feature` | DB, fixtures, mock externals |

**All new classes MUST have test coverage.**

---

## 9. BDD Testing (Cli & Web)

See detailed guide: `docs/02-onboarding/04-testing.md` (section "BDD Testing")

**Key points:**

- Register steps in `Cli.suite.yml` / `Web.suite.yml` under `gherkin.contexts.tag`
- Use explicit data tables in feature files, not hidden data in steps
- Target 20-30 reusable steps, use `data-test` attributes for selectors
- Each feature should have 15+ scenarios covering all roles, edge cases, errors
- Write scenarios BEFORE implementation

---

## 10. Documentation

### Location

```
docs/
├── 01-project-overview/     # Vision, business domain, glossary
├── 02-onboarding/           # Quick start, tooling, structure, testing, workflow
├── 03-decisions/            # ADRs (architectural decisions)
└── 04-feature-requests/     # Task specifications
```

### MANDATORY: Update Documentation on Changes

**When project structure changes, documentation MUST BE updated:**

| Change Type                           | Files to Update                                              |
|---------------------------------------|--------------------------------------------------------------|
| New Bounded Context                   | `02-business-domain.md`, `03-structure.md`, `03-glossary.md` |
| New Entity/Aggregate                  | `02-business-domain.md`                                      |
| Architecture decision                 | Create new ADR in `03-decisions/`                            |
| New tool or command, new make command | `02-tooling.md`, this file (AGENTS.md)                       |
| Layer structure change                | `03-structure.md`                                            |
| Testing approach change               | `04-testing.md`                                              |
| Workflow/Git process change           | `05-workflow.md`                                             |

---

## 11. AI Agent Instructions

### WORKFLOW (follow in order)

1. **START** — Verify you're in project root directory

2. **READ CONTEXT**
    - This file (`AGENTS.md`) — architecture, rules, commands
    - `docs/04-feature-requests/BACKLOG.md` — task details with full context
    - `docs/04-feature-requests/PROJECT-STATUS.md` — current focus and progress
    - Relevant ADRs in `docs/03-decisions/`
    - For new agents: `docs/01-project-overview/` (vision, domain, glossary)

3. **BEFORE CODING**
    - Identify Bounded Context
    - Verify dependency rules (section 4)

4. **IMPLEMENT**
    - Place code in correct layer (section 3)
    - Follow layer rules (section 7)
    - Use ONLY `make` commands (section 2)

5. **TEST** — Follow Testing Trophy (section 8). Integration tests first.

6. **BEFORE COMMIT**
    - Run `make lp` — must pass
    - Run `make ps` — must pass
   - **Add all created/modified files to git:** `git add <file>` for each file
   - Stage files incrementally as you work, not all at once at the end

7. **BEFORE PUSH**
    - Run `make scan` — **MANDATORY**, must pass

8. **AFTER STRUCTURAL CHANGES**
    - Update documentation per section 10
    - Update this file if commands changed

9. **REVIEW** — Simplify implementation where possible

### CONSTRAINTS

- **ONLY `make` commands** — never run composer/vendor/bin directly
- **Git staging required** — always `git add <file>` for created/modified files before commit
- **Documentation sync** — always update docs when structure changes
- **English only** — all documentation, comments, and commit messages must be in English
- **No emojis** — never use emojis in code, documentation, comments, or commit messages
- **Tests required** — all new classes must have coverage
- **Dependency law** — outer depends on inner, never reverse

---

*This file is the source of truth for AI agents working on BoardGameLog.*
