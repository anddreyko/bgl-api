# Testing

BoardGameLog uses **Codeception 5.3** for all test types. The testing strategy is based on the **Testing Trophy**
concept rather than the traditional pyramid.

---

## Testing Trophy

Unlike the classic testing pyramid where unit tests form the base, we follow the Testing Trophy approach. The trophy
shape reflects effort distribution: the wide middle (functional + integration tests) is the foundation of system
confidence.

```
          +----------------+
          |   End-to-End   |       <- few
      +---+--------+-------+----+
      |       Integration       |  <- many (foundation)
      +---+--------+-------+----+
      |        Functional       |  <- many (foundation)
      +-------+--------+--------+
              |  Unit  |           <- few
      +-------+--------+-------+
      |   Static Analysis      |   <- base
      +------------------------+
```

**Trophy Levels (bottom to top):**

1. **Static Code Analysis** -- code verification without execution. Psalm checks types and potential errors,
   PHP-CS-Fixer ensures style consistency, Deptrac controls architectural dependencies. Catches an entire class of
   errors without writing tests.

2. **Unit Tests (few)** -- isolated tests for complex pure logic. Written only for Domain + Core classes with
   non-trivial invariants or validation. Zero external dependencies, zero stubs.

3. **Functional + Integration Tests (many)** -- the main confidence level. Functional tests verify application-layer
   use cases with InMemory/Fake dependencies. Integration tests verify infrastructure contracts with real backing
   services. Best cost/benefit ratio.

4. **End-to-End Tests (few)** -- verification of key user scenarios via HTTP API or CLI. Cover critical happy paths
   and access control, not every endpoint.

---

## Testing Framework

The project uses **Codeception** for all test types, including unit tests. PHPUnit is not directly supported due to
external tooling constraints (vendor-bin isolation, CI configuration, coverage reports).

All tests, including unit tests, are written in Codeception style using Cest classes and Tester objects. This ensures
approach uniformity and simplifies test infrastructure maintenance.

```php
// Correct -- Codeception style
final class EmailCest
{
    public function testValidEmail(UnitTester $I): void
    {
        $email = new Email('test@example.com');

        $I->assertEquals('test@example.com', $email->value);
    }
}

// Wrong -- PHPUnit style (not supported)
final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        // ...
    }
}
```

---

## Test Types and Layer Mapping

Each test suite maps to exactly one architecture layer. See ADR-015 for the decision rationale.

| Suite       | Layer              | What to Test                          | DI Container    | Backing Services |
|-------------|--------------------|---------------------------------------|-----------------|------------------|
| Unit        | Domain + Core pure | Invariants, validation, method logic  | No              | No               |
| Functional  | Application        | Handlers, use cases, state changes    | Yes (InMemory)  | No               |
| Integration | Infrastructure     | Repos, adapters, contract compliance  | Yes (real)      | Yes              |
| Web / Cli   | Presentation       | Happy paths, access control           | N/A (HTTP/CLI)  | Yes              |

### Unit Tests (Domain + Core Pure)

**Layer:** Domain entities, Value Objects, Core pure classes (exceptions, collections).

**Location:** `tests/Unit/`

**Rules:**

- Zero external dependencies, zero DI, zero database
- Zero `Stub::makeEmpty()` -- if a class needs stubs to test, it is not a Unit test
- Test invariants, validation logic, method behavior of self-contained classes

**When to write:** Value Objects with validation, entities with invariant logic, complex pure calculations. Not needed
for simple getters, delegating methods, trivial operations.

```bash
composer test:unit
```

```php
final class EmailCest
{
    public function testValidEmail(UnitTester $I): void
    {
        $email = new Email('test@example.com');

        $I->assertEquals('test@example.com', $email->value);
    }

    public function testInvalidEmailThrowsException(UnitTester $I): void
    {
        $I->expectThrowable(InvalidArgumentException::class, function () {
            new Email('invalid-email');
        });
    }
}
```

### Functional Tests (Application Layer)

**Layer:** Application handlers, use cases, aspects.

**Location:** `tests/Functional/`

**Rules:**

- DI container with InMemory/Fake bindings (no real database)
- Execute a handler, verify system state changes via InMemory repositories
- Infrastructure replaced at runtime via `Container::set()` in Codeception module
- InMemory repos, FakeConfirmer, FakeTokenIssuer, NullTransactor

**When to write:** For every handler / use case. This is the primary test type for business logic.

```bash
composer test:func
```

```php
final class SignUpHandlerCest
{
    public function testSignsUpUser(FunctionalTester $I): void
    {
        $command = new SignUpCommand(
            email: 'user@example.com',
            password: 'SecurePass1!',
        );

        $I->handleCommand($command);

        $I->seeUserInRepository('user@example.com');
    }

    public function testFailsOnDuplicateEmail(FunctionalTester $I): void
    {
        $I->haveUserInRepository('user@example.com');

        $command = new SignUpCommand(
            email: 'user@example.com',
            password: 'SecurePass1!',
        );

        $I->expectThrowable(UserAlreadyExistsException::class, function () use ($I, $command) {
            $I->handleCommand($command);
        });
    }
}
```

### Integration Tests (Infrastructure Layer)

**Layer:** Repository implementations, external adapters, infrastructure services.

**Location:** `tests/Integration/`

**Rules:**

- Real DI container (`APP_ENV=test`), real database
- Contract test pattern: abstract base class defines test methods, concrete class provides implementation via
  factory method
- Tests use a separate test database with fixtures
- Each test runs in a transaction that is rolled back after completion

**When to write:** For all repository implementations, external service adapters, infrastructure contracts.

```bash
composer test:intg
```

```php
final class DoctrinePlaysCest
{
    public function testFindsPlayById(IntegrationTester $I): void
    {
        $play = PlayFactory::create();
        $I->haveInDatabase($play);

        $found = $I->grabRepository(Plays::class)->findById($play->id());

        $I->assertNotNull($found);
        $I->assertEquals($play->id(), $found->id());
    }
}
```

### End-to-End Tests (Presentation Layer)

**Layer:** Presentation -- HTTP API (Web) and CLI commands.

**Location:** `tests/Web/` (API), `tests/Cli/` (CLI)

**Rules:**

- Full stack: real HTTP requests or CLI execution, real database
- Only happy-path scenarios + access control checks (authenticated/unauthenticated, roles)
- Edge cases and error paths are covered by Functional and Unit tests

**When to write:** For critical user scenarios -- registration, authentication, session creation. Not needed for
every endpoint.

```bash
composer test:web    # API tests
composer test:cli    # CLI tests
```

**Principles:**

- **Happy path + access control** -- acceptance tests verify that the system works end-to-end and that access rules
  are enforced. Edge cases are covered by Functional tests.
- **Protected endpoints require Bearer token** -- all requests to protected routes need
  `Authorization: Bearer {token}`. Use `AuthModule` for automatic token retrieval in tests.
- **Test data via API + Db module** -- test data is created through HTTP requests (sign-up, sign-in) and Codeception
  Db module methods (`updateInDatabase`, `grabFromDatabase`) for state preparation.
- **Automatic cleanup** -- Db module with `cleanup: true` wraps each test in a transaction and rolls it back afterward.

---

## Test Doubles

`Stub::makeEmpty()` is **phased out**. Use InMemory/Fake implementations provided via DI instead.

**Why:** Stubs test implementation details (which methods are called), not behavior. InMemory/Fake implementations
test actual state changes and are reusable across all Functional tests.

### InMemory Repositories

Location: `src/Infrastructure/Persistence/InMemory/`

Concrete classes that implement domain repository interfaces with simple in-memory array storage. Inherit from
`InMemoryRepository` base class.

### Fake / Null Services

Location: `tests/Support/Dummy/`

- `FakeConfirmer` -- confirms tokens without external service
- `FakeTokenIssuer` -- issues tokens without cryptographic signing
- `NullTransactor` -- executes callback directly without transaction wrapping

### DI Replacement Mechanism

Functional tests use a Codeception module that replaces real DI bindings with InMemory/Fake implementations via
PHP-DI `Container::set()` at runtime. This is configured in `Functional.suite.yml`.

---

## Testing Commands

| Command                  | Purpose              |
|--------------------------|----------------------|
| `composer test:unit`     | Unit tests           |
| `composer test:func`     | Functional tests     |
| `composer test:intg`     | Integration tests    |
| `composer test:web`      | Acceptance API tests |
| `composer test:cli`      | Acceptance CLI tests |
| `composer test:all`      | All tests + mutation testing |
| `composer test:coverage` | Coverage report              |
| `composer in:ps`         | Mutation testing (Psalm)     |
| `composer in:run`        | Mutation testing (Infection) |

---

## Static Analysis (Foundation)

Runs before all tests. Checks types, finds potential bugs, controls architecture.

```bash
composer scan:style     # PHP-CS-Fixer + Rector (modifies code)
composer scan:php       # Lint + Psalm
composer scan:depend    # Deptrac + Composer dependencies
composer scan:all       # scan:php + scan:depend + test:all + in:ps (without scan:style)
```

The `scan:all` command intentionally excludes `scan:style` since it modifies code. Run `scan:style` separately first,
then `scan:all` for verification.

---

## Mutation Tests (Automatic Quality Check)

Mutation testing is an automatic quality check of existing tests. Infection makes small changes (mutations) to the code
and verifies that tests detect these changes. Uses Roave Infection Static Analysis Plugin (Psalm-based) for enhanced
detection.

Mutation testing runs **automatically as the last step of `test:all`** (and therefore `scan:all`), after all test suites
pass. It can also be run standalone:

```bash
composer in:ps       # Mutation testing with Psalm (recommended)
composer in:run      # Mutation testing with Infection only (min-msi=70, min-covered-msi=80)
```

### Configuration

Configuration file: `infection.json`. Key settings:

| Setting              | Value              | Description                                    |
|----------------------|--------------------|------------------------------------------------|
| `threads`            | 2                  | Parallel mutation processes                    |
| `timeout`            | 10                 | Seconds before killing a mutation run          |
| `testFramework`      | codeception        | Test framework adapter                         |
| `testFrameworkOptions` | Unit,Functional  | Test suites used to kill mutants               |
| `source.directories` | `["src"]`          | Source code directories to mutate              |

Reports are generated in `var/.infections/` (infection.log, summary.log, infection.json, per-mutator.md).

Memory: infection requires ~210MB, configured via `php -d memory_limit=512M` in composer scripts (default PHP
`memory_limit=128M` is insufficient).

### Workflow

Mutation tests don't need to be written manually. They run automatically based on existing tests. The developer's task
is to ensure mutation tests pass, meaning existing tests are good enough to "kill" mutations.

If a mutation "survives" (tests don't fail when code changes), it signals insufficient coverage or weak assertions. In
this case, strengthen existing tests rather than writing new mutation tests.

---

## Performance Benchmarks

PHPBench measures execution time of critical code paths. Benchmarks catch performance regressions before they reach
production.

### Running Benchmarks

Two modes of operation:

```bash
composer bm:run     # Quick run -- execute all benchmarks, see current timings
composer bm:base    # Create baseline snapshot (store current results)
composer bm:check   # Assert no regression vs baseline (fails if > 10% slower)
```

The baseline workflow (`bm:base` + `bm:check`) is useful before and after refactoring: create a baseline, make changes,
then verify no regression.

### Benchmark Categories

| Category    | Location                          | What It Covers                                    |
|-------------|-----------------------------------|---------------------------------------------------|
| Core        | `tests/Benchmark/Core/`           | Value Objects creation, validation, comparison     |
| Domain      | `tests/Benchmark/Domain/`         | Entity creation, business methods, state changes   |
| Handlers    | `tests/Benchmark/Handlers/`       | Use case execution (handler + dependencies)        |
| Http        | `tests/Benchmark/Http/`           | Routing, serialization, middleware pipeline         |
| Persistence | `tests/Benchmark/Persistence/`    | Repository operations, query building, hydration   |

### When to Write Benchmarks

- New handler or use case with performance expectations
- New repository method (especially with complex queries)
- New serialization or hydration logic
- Performance-sensitive domain logic (calculations, collections)
- New infrastructure adapter (external API clients, cache)

### Configuration

Config file: `phpbench.json`. Key settings:

- **Retry threshold:** 5% -- reruns unstable iterations to reduce noise
- **Assertion:** baseline +/- 10% -- fails if performance degrades more than 10%

Benchmark files location: `tests/Benchmark/{Layer}/`

---

## Development Priority

When creating a new feature, follow the Quality Pipeline order:

```
0. Code Style Fix       Rector + PHPCBF                                <- modifies code, run FIRST
         |                composer scan:style
1. Dependency Check     Composer Dependency Analyser                   <- composer.json integrity
         |                composer cd
2. Static Analysis      PHP Lint, Psalm, PDepend                      <- syntax, types, complexity
         |                composer lp:run, composer ps:run, composer pd:check
3. Architecture         Deptrac                                        <- dependency law enforcement
         |                composer dt:run
4. API Contract         OpenAPI Export + Validate                      <- spec consistency
         |                composer oa:run
5. Unit Tests           Codeception Unit                               <- complex logic only
         |                composer test:unit
6. Integration Tests    Codeception Integration, Functional            <- MAIN FOCUS
         |                composer test:intg, composer test:func
7. Acceptance Tests     Codeception Smoke, Web, Cli                    <- happy paths + access control
         |                composer test:smoke, composer test:web, composer test:cli
8. Mutation Testing     Infection + Psalm                              <- test quality gate
         |                composer in:ps
9. Benchmarks           PHPBench                                       <- performance regression (optional)
                          composer bm:check
```

Steps 1-8 = `composer scan:all`. Step 0 = `composer scan:style` (run separately before). Step 9 is optional.

Don't aim for 100% unit test coverage. Functional + Integration tests provide more confidence with less maintenance cost.

---

## BDD Testing (Cli & Web)

*Status: planned. Infrastructure exists, conventions below apply when writing BDD scenarios.*

### Architecture

```
Feature File (*.feature)
    | Gherkin syntax
Step Definition (*Steps.php)
    | thin layer, delegates to traits
Trait (*Trait.php)
    | actual implementation, organized by data model
```

| Suite | Features              | Steps                                | Traits                      |
|-------|-----------------------|--------------------------------------|-----------------------------|
| Cli   | `tests/Cli/*.feature` | `tests/_support/Step/Cli/*Steps.php` | `CliTrait/{Model}Trait.php` |
| Web   | `tests/Web/*.feature` | `tests/_support/Step/Web/*Steps.php` | `WebTrait/{Model}Trait.php` |

### CRITICAL: Register Steps in Suite Config

Steps MUST be registered in suite config (`Cli.suite.yml` or `Web.suite.yml`) under `gherkin.contexts.tag`. Without
registration, tests show as "incomplete".

### Feature File Rules

**DO: Explicit data in feature files**

```gherkin
Given the following posts exist:
| id | title        | author |
| 1  | First Post   | alice  |
| 2  | Second Post  | bob    |
```

**DON'T: Hide data in steps**

```gherkin
Given 3 posts exist  # Data hidden in step implementation
```

### Step Implementation Rules

1. Keep steps minimal: click, type, check presence, visit URL, verify URL
2. Target 20-30 reusable steps, not hundreds of specialized ones
3. Use `data-test` attributes for selectors, never CSS paths like `div > ul > li:nth-child(3)`
4. Treat URLs as UI elements, use raw URLs not page name abstractions

### Scenario Requirements

- Each feature MUST have exhaustive scenarios covering all user roles, edge cases, error states
- A simple feature should generate 15+ scenarios when properly analyzed
- Write scenarios BEFORE implementation
- Scenarios are the single source of truth for designers, managers, and developers

### Anti-Patterns (AVOID)

1. Implicit ordering dependencies in seeded data
2. Steps with hidden side effects
3. Coupling steps to specific test contexts
4. Using step implementations as source of truth
5. Creating unique steps for individual tests
6. Structural/positional CSS selectors
7. Page object names instead of actual URLs
