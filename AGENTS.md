# AGENTS.md – BoardGameLog API

## 1. Project Context

**BoardGameLog (BGL)** — API platform for tracking board game sessions.

**Core Stack:** PHP 8.4, PostgreSQL, Slim 4, Doctrine ORM, Codeception

**Architecture:** Clean Architecture, DDD, CQS, Ports & Adapters, Mediator Pattern, Middleware-based AOP, SOLID
Principles

**Always operate from the project root directory.**

---

## 2. Commands (ONLY via `composer`)

**CRITICAL: Never run tools directly. All commands via `composer` only.**

### Code Quality

| Command             | Purpose                                           |
|---------------------|---------------------------------------------------|
| `composer cs:fix`   | Fix code style (PHP-CS-Fixer)                     |
| `composer rc:run`   | Apply automated refactoring (Rector)              |
| `composer lp:run`   | PHP syntax check (Lint)                           |
| `composer ps:run`   | Static type analysis (Psalm)                      |
| `composer pd:check` | Code complexity check (PDepend)                   |
| `composer dt:run`   | Architecture dependency check (Deptrac) — **LAW** |
| `composer cd:run`   | Check composer dependencies                       |
| `composer scan:all` | **MANDATORY before push.** Full validation.       |

### Testing

| Command                  | Purpose                  |
|--------------------------|--------------------------|
| `composer test:all`      | Run all test suites      |
| `composer test:unit`     | Unit tests               |
| `composer test:func`     | Functional tests         |
| `composer test:intg`     | Integration tests        |
| `composer test:web`      | Acceptance API tests     |
| `composer test:cli`      | Acceptance CLI tests     |
| `composer test:coverage` | Generate coverage report |
| `composer in:ps`         | Mutation testing         |

### Database

| Command                | Purpose                                  |
|------------------------|------------------------------------------|
| `make wait-db`         | Wait for database readiness              |
| `make migrate`         | Run all pending migrations               |
| `make migrate-gen`     | Generate migration diff from ORM mapping |
| `make migrate-empty`   | Generate empty migration class           |
| `make validate-schema` | Validate ORM schema against database     |
| `make load-fixtures`   | Load fixtures into database              |

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
│   ├── Security/            # Security contracts (PasswordHasher)
│   └── ValueObjects/
│
├── Domain/                  # Business logic by Bounded Context
│   ├── Games/               # Game catalog (Game, Games)
│   ├── Mates/               # Co-player directory (Mate, Mates)
│   ├── Plays/               # Play logging (Play, Plays, PlayStatus, Visibility)
│   │   └── Player/          # Child entity (Player, Players, PlayersFactory, EmptyPlayers)
│   ├── Profile/             # User identity & profile (User, Users, UserId, UserStatus)
│   │   └── Passkey/         # Child entity (Passkey, Passkeys, PasskeyChallenge, PasskeyChallenges)
│   └── Stats/               # Analytics & statistics
│
├── Application/             # Use cases
│   ├── Aspects/             # Middleware (Logging, Transactional, etc.)
│   └── Handlers/            # One handler = one use case
│       └── {Context}/{UseCase}/
│           ├── Command.php  # or Query.php
│           └── Handler.php
│
├── Infrastructure/          # External services & adapters
│   ├── Database/
│   │   └── Migrations/      # Doctrine database migrations
│   ├── Persistence/
│   │   ├── Doctrine/        # Repository implementations
│   │   └── InMemory/        # For tests
│   ├── MessageBus/Tactician/
│   ├── Security/            # Security adapters (BcryptPasswordHasher)
│   └── Clients/Bgg/         # BoardGameGeek adapter
│
└── Presentation/            # Entry points
    ├── Api/                 # HTTP API
    └── Console/             # CLI commands
```

---

## 4. Architectural Dependency Law

**Inner layers never depend on outer layers. Enforced by `composer dt:run`.**

```
Infrastructure → Application → Domain → Core
Presentation  ↗
```

**Dependency direction:** Outer layers depend on inner layers, never reverse.

---

## 5. Bounded Contexts

| Context | Responsibility                          | Phase | Location          |
|---------|----------------------------------------|-------|-------------------|
| Profile | User identity, profile, settings        | 1+    | `Domain/Profile/` |
| Plays   | Play logging, players, locations        | 1     | `Domain/Plays/`   |
| Mates   | Personal co-player directory            | 1     | `Domain/Mates/`   |
| Games   | Game catalog (on-demand BGG import)     | 1     | `Domain/Games/`   |
| Stats   | Analytics and reporting                 | 1+    | `Domain/Stats/`   |
| Access  | Auth methods, passkeys, device sessions | 4     | `Domain/Access/`  |

**Not bounded contexts:** Auth (infrastructure: `Core/Auth/` + `Infrastructure/Auth/`), Sync (infrastructure:
`Core/Sync/` + `Infrastructure/Sync/`). Passkey/Password are auth infrastructure, will migrate to Access in Phase 4.

---

## 6. Key Patterns

**Full guide with examples:** `docs/02-onboarding/08-code-conventions.md`

- **Design Patterns:** Decorator (Aspects), Bridge (Ports & Adapters), Adapter (external libs), Proxy (lazy-loading), RAII (Transactional) -- see section 12
- **Persistence (ORM-agnostic):** zero ORM deps on Entity, mapping in Infrastructure, ORM swappable via DI, lazy loading + batch ops required -- see section 14
- **Aspects:** configured in DI as middleware, NOT as attributes
- **Domain Events:** processed within transactions, NOT stored (no Event Sourcing in MVP). See ADR-006
- **Testing Trophy:** Static Analysis -> Integration -> Unit -> E2E. Integration = main confidence source

---

## 7. Code Rules

**Full guide with examples:** `docs/02-onboarding/08-code-conventions.md`

### General

- PSR-12 strictly
- `declare(strict_types=1);` in every file
- `PascalCase` classes, `camelCase` methods/variables, `UPPER_SNAKE_CASE` constants
- `final` for all concrete classes, `readonly` where possible
- Constructor property promotion, named arguments for 3+ params
- Interfaces without prefix/suffix: `Mates`, not `MatesInterface`
- `#[\Override]` on all methods overriding parent/interface

### Type System

- Generics via docblocks: `@template T`, `@extends Repository<Entity>`, `@implements Message<Result>`
- `non-empty-string` in docblock for strings that must not be empty
- No `mixed` unless at external library boundaries
- **No arrays in public contracts** -- use ClassMap, Iterator/IteratorAggregate, typed collections, or readonly DTO
- `array` allowed only inside `private` implementation details; exceptions only by project owner

### Domain Context Structure

Aggregate root at context root, child entities in subdirectories. No `Entities/` or `Exceptions/` folders.
No domain services. Cross-context interaction only via Domain Events. See `docs/02-onboarding/08-code-conventions.md` section 13.

### By Layer

| Layer         | Location                                       | Rules                                                              |
|---------------|------------------------------------------------|--------------------------------------------------------------------|
| Core          | `Core`                                         | Non-domain contracts, shared Value Objects                         |
| Value Objects | `Core/ValueObjects/`, `Domain/*/`              | `final readonly`, validate in constructor, immutable               |
| Entities      | `Domain/*/`, `Domain/*/{ChildEntity}/`         | Private ctor + `static create()`, business methods, no setters     |
| Enums         | `Domain/*/`                                    | `enum Name: string`, lowercase backing values                      |
| Repositories  | Contracts: `Domain/*/`, Impl: Infrastructure   | **Collections.** Return Entity/VO/scalar (last resort), no DTO     |
| Handlers      | `Application/Handlers/`                        | `MessageHandler<R, M>` universal contract, one handler = one case  |
| Results       | `Application/Handlers/`                        | Type-safe objects (VO, entities), NOT primitives-only              |
| Presentation  | `Presentation/Api/`, `Presentation/Console/`   | Entity→JSON mapping here (middleware/handler), RESTful strictly    |

### Exceptions (hybrid)

- Core (`\RuntimeException`): `NotFoundException`, `AccessDeniedException`, `AuthenticationException`
- Domain (`\DomainException`): per-context named subclasses (`PlayNotDraftException`, etc.)
- **Never** throw bare `\DomainException` or `\RuntimeException`

### RESTful API

- URL: `/v1/{resource}/{id}`, plural nouns, no verbs
- Response envelope: `{ "code": 0, "data": {...} }` / `{ "code": 1, "message": "..." }`
- Logging strictly by categories via Logging aspect

---

## 8. Workflow & Testing

**Full guides:** `docs/02-onboarding/05-workflow.md`, `docs/02-onboarding/04-testing.md`

### Quality Gates

- **Before commit:** `composer lp:run`, `composer ps:run`
- **Before push:** `composer scan:all` -- **MANDATORY**

### Testing Order (Trophy)

1. Static Analysis: `composer lp:run`, `composer ps:run`, `composer dt:run`
2. Functional & Integration Tests: `composer test:func`, `composer test:intg` -- **main focus**
3. Unit Tests: `composer test:unit` -- complex logic only
4. Acceptance Tests: `composer test:web`, `composer test:cli`

### TDD

- New functionality OR tests exist -- TDD
- Bug fix / refactoring without tests -- write tests after
- **All new classes MUST have test coverage**

### BDD (Cli & Web)

- Write scenarios BEFORE implementation
- Register steps in suite configs under `gherkin.contexts.tag`
- Explicit data tables in feature files, 15+ scenarios per feature

---

## 10. Documentation

**Location:** `docs/` -- `01-project-overview/`, `02-onboarding/`, `03-decisions/` (ADRs), `04-feature-requests/`

**MANDATORY:** When project structure changes, update corresponding docs. Architecture decisions go to `03-decisions/` as ADRs.

---

## 11. AI Agent Instructions

### Workflow

1. Read context: this file, Beads (`bd list`), relevant ADRs
2. Identify Bounded Context, verify dependency rules (section 4)
3. Implement: correct layer (section 3), layer rules (section 7), ONLY `composer` commands
4. Test: Testing Trophy (section 8), integration first
5. Before commit: `composer lp:run` + `composer ps:run` must pass, `git add` each file
6. Before push: `composer scan:all` -- **MANDATORY**
7. After structural changes: update docs (section 10)

### Constraints

- ONLY `composer` commands -- never vendor/bin directly
- English only in docs, comments, commits. No emojis
- All new classes MUST have test coverage
- Dependency law -- outer depends on inner, never reverse
- Documentation sync -- always update docs when structure changes

### Session Completion

1. Create issues for remaining work
2. Run quality gates
3. Push: `git pull --rebase && bd sync && git push`
4. Verify: all changes committed AND pushed

**CRITICAL RULES:**

- Work is NOT complete until `git push` succeeds
- NEVER stop before pushing - that leaves work stranded locally
- NEVER say "ready to push when you are" - YOU must push
- If push fails, resolve and retry until it succeeds

---

*This file is the source of truth for AI agents working on BoardGameLog.*
