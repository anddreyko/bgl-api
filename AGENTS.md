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
│   ├── Games/               # Game catalog
│   ├── Mates/               # Co-player directory
│   ├── Plays/               # Play logging
│   ├── Profile/             # User identity & profile
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

### Ports & Adapters

Domain defines interfaces, Infrastructure implements:
`Domain/Sync/PlaySynchronizer` → `Infrastructure/Sync/Bgg/BggPlaySynchronizer`

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

| Stage         | Command                              | Required      |
|---------------|--------------------------------------|---------------|
| Before commit | `composer lp:run`, `composer ps:run` | Yes           |
| Before push   | `composer scan:all`                  | **MANDATORY** |

### Testing Order (Trophy)

1. Static Analysis: `composer lp:run`, `composer ps:run`, `composer dt:run`
2. Functional & Integration Tests: `composer test:func`, `composer test:intg` — **main focus**
3. Unit Tests: `composer test:unit` — complex logic only
4. Acceptance Tests: `composer test:web`, `composer test:cli`
5. Mutation Testing: `composer in:ps`

### TDD Rules

| When                                | Approach          |
|-------------------------------------|-------------------|
| New functionality OR tests exist    | Use TDD           |
| Bug fix / refactoring without tests | Write tests after |

### Test Placement

See detailed guide: `docs/02-onboarding/04-testing.md` (layer mapping table, test doubles, examples).

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

| Change Type                              | Files to Update                                              |
|------------------------------------------|--------------------------------------------------------------|
| New Bounded Context                      | `02-business-domain.md`, `03-structure.md`, `03-glossary.md` |
| New Entity/Aggregate                     | `02-business-domain.md`                                      |
| Architecture decision                    | Create new ADR in `03-decisions/`                            |
| New tool or command, new composer script | `02-tooling.md`, this file (AGENTS.md)                       |
| Layer structure change                   | `03-structure.md`                                            |
| Testing approach change                  | `04-testing.md`                                              |
| Workflow/Git process change              | `05-workflow.md`                                             |
| AI commands or build process change      | `06-ai-development.md`                                       |

---

## 11. AI Agent Instructions

### WORKFLOW (follow in order)

1. **START** — Verify you're in project root directory

2. **READ CONTEXT**
    - This file (`AGENTS.md`) — architecture, rules, commands
    - Beads (`bd list`, `bd show {ID}`) — task tracking, descriptions, dependencies
    - Relevant ADRs in `docs/03-decisions/`
    - For new agents: `docs/01-project-overview/` (vision, domain, glossary)

3. **BEFORE CODING**
    - Identify Bounded Context
    - Verify dependency rules (section 4)

4. **IMPLEMENT**
    - Place code in correct layer (section 3)
    - Follow layer rules (section 7)
    - Use ONLY `composer` commands (section 2)

5. **TEST** — Follow Testing Trophy (section 8). Integration tests first.

6. **BEFORE COMMIT**
    - Run `composer lp:run` — must pass
    - Run `composer ps:run` — must pass
   - **Add all created/modified files to git:** `git add <file>` for each file
   - Stage files incrementally as you work, not all at once at the end

7. **BEFORE PUSH**
    - Run `composer scan:all` — **MANDATORY**, must pass

8. **AFTER STRUCTURAL CHANGES**
    - Update documentation per section 10
    - Update this file if commands changed

9. **REVIEW** — Simplify implementation where possible

### CONSTRAINTS

- **ONLY `composer` commands** — never run vendor/bin directly
- **Git staging required** — always `git add <file>` for created/modified files before commit
- **Documentation sync** — always update docs when structure changes
- **English only** — all documentation, comments, and commit messages must be in English
- **No emojis** — never use emojis in code, documentation, comments, or commit messages
- **Tests required** — all new classes must have coverage
- **Dependency law** — outer depends on inner, never reverse

---

*This file is the source of truth for AI agents working on BoardGameLog.*

## Landing the Plane (Session Completion)

**When ending a work session**, you MUST complete ALL steps below. Work is NOT complete until `git push` succeeds.

**MANDATORY WORKFLOW:**

1. **File issues for remaining work** - Create issues for anything that needs follow-up
2. **Run quality gates** (if code changed) - Tests, linters, builds
3. **Update issue status** - Close finished work, update in-progress items
4. **PUSH TO REMOTE** - This is MANDATORY:
   ```bash
   git pull --rebase
   bd sync
   git push
   git status  # MUST show "up to date with origin"
   ```
5. **Clean up** - Clear stashes, prune remote branches
6. **Verify** - All changes committed AND pushed
7. **Hand off** - Provide context for next session

**CRITICAL RULES:**

- Work is NOT complete until `git push` succeeds
- NEVER stop before pushing - that leaves work stranded locally
- NEVER say "ready to push when you are" - YOU must push
- If push fails, resolve and retry until it succeeds
