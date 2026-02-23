# Testing

BoardGameLog uses **Codeception 5.3** for all test types. The testing strategy is based on the **Testing Trophy**
concept rather than the traditional pyramid.

---

## Testing Trophy

Unlike the classic testing pyramid where unit tests form the base, we follow the Testing Trophy approach. The trophy
shape reflects effort distribution: the wide middle (integration tests) is the foundation of system confidence.

```
       ╱‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾╲
      │      End-to-End   │       ← Few E2E tests
       ╲_________________╱
      ╱                   ╲
     │                     │
     │    Integration      │      ← Many integration (foundation)
     │                     │
      ╲___________________╱
       ╱                 ╲
      │       Unit        │       ← Few unit tests
       ╲_________________╱
      │                   │
      │  Static Analysis  │       ← Base
      │                   │
      ╰───────────────────╯
```

**Trophy Levels (bottom to top):**

1. **Static Code Analysis** — code verification without execution. Psalm checks types and potential errors, PHP-CS-Fixer
   ensures style consistency, Deptrac controls architectural dependencies. Catches an entire class of errors without
   writing tests.

2. **Unit Tests (few)** — isolated tests for complex business logic. Written only where there are non-trivial
   calculations or rules. Not needed for simple operations.

3. **Integration Tests (many)** — the main confidence level. Verify component interaction: Handler + Repository,
   Repository + Database. Best cost/benefit ratio.

4. **End-to-End Tests (few)** — verification of key user scenarios via API. Cover critical paths but not every endpoint.

**Approach Benefits:**

Static analysis catches simple errors without writing tests. Integration tests provide system confidence with less
dependency on internal implementation than unit tests. Implementation changes don't break tests as long as behavior
remains correct.

---

## Testing Framework

The project uses **Codeception** for all test types, including unit tests. PHPUnit is not directly supported due to
external tooling constraints (vendor-bin isolation, CI configuration, coverage reports).

All tests, including unit tests, are written in Codeception style using Cest classes and Tester objects. This ensures
approach uniformity and simplifies test infrastructure maintenance.

```php
// Correct — Codeception style
final class EmailCest
{
    public function testValidEmail(UnitTester $I): void
    {
        $email = new Email('test@example.com');

        $I->assertEquals('test@example.com', $email->value);
    }
}

// Wrong — PHPUnit style (not supported)
final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        // ...
    }
}
```

---

## Test Types

### Static Analysis (Foundation)

Runs before all tests. Checks types, finds potential bugs, controls architecture.

```bash
composer scan:style     # PHP-CS-Fixer + Rector (modifies code)
composer scan:php       # Lint + Psalm
composer scan:depend    # Deptrac + Composer dependencies
composer scan:all       # scan:php + scan:depend + test:all (without scan:style)
```

The `scan:all` command intentionally excludes `scan:style` since it modifies code. Run `scan:style` separately first,
then `scan:all` for verification.

### Unit Tests (Few)

Isolated tests of individual classes. All dependencies are mocked. Written only for complex logic. Use Codeception
UnitTester.

Location: `tests/Unit/`

```bash
composer test:unit
```

When to write: Value Objects with validation, domain services with calculations, complex business rules. Not needed for
simple getters, delegating methods, trivial operations.

### Integration Tests (Foundation)

The main test type in the project. Verify component interaction with test database and fixtures.

Location: `tests/Integration/`

```bash
composer test:intg
```

When to write: for all Handlers, Repository implementations, external integrations (BGG API). This is the main source of
confidence in system operability. Tests use a separate test database with fixtures, not production data.

### Functional Tests

Business scenario tests with InMemory repositories. Faster than integration, verify business logic without DB.

Location: `tests/Functional/`

```bash
composer test:func
```

### End-to-End Tests (Few)

E2E tests via HTTP API and CLI. Verify the full path from entry point to database.

Location: `tests/Web/` (API), `tests/Cli/` (CLI)

```bash
composer test:web    # API tests
composer test:cli    # CLI tests
```

When to write: for critical user scenarios — registration, session creation, statistics retrieval. Not needed for every
endpoint.

**Principles:**

- **Happy path only** — acceptance tests verify that the system works end-to-end. A basic scenario: a user can register,
  log in, perform an action, and log out. Business logic details (validation, errors, edge cases) are covered by
  functional and unit tests.
- **Protected endpoints require Bearer token** — all requests to protected routes need `Authorization: Bearer {token}`.
  Use `AuthModule` for automatic token retrieval in tests.
- **Test data via API + Db module** — test data is created through HTTP requests (sign-up, sign-in) and Codeception Db
  module methods (`updateInDatabase`, `grabFromDatabase`) for state preparation (e.g. confirming a user).
- **Automatic cleanup** — Db module with `cleanup: true` wraps each test in a transaction and rolls it back afterward.

### Mutation Tests (Automatic Quality Check)

Mutation testing is an automatic quality check of existing tests. Infection makes small changes (mutations) to the code
and verifies that tests detect these changes.

```bash
composer in          # Run mutation testing
```

Mutation tests don't need to be written manually. They run automatically based on existing tests. The developer's task
is to ensure mutation tests pass, meaning existing tests are good enough to "kill" mutations.

If a mutation "survives" (tests don't fail when code changes), it signals insufficient coverage or weak assertions. In
this case, strengthen existing tests rather than writing new mutation tests.

---

## Testing Commands

| Command                  | Purpose              |
|--------------------------|----------------------|
| `composer test:unit`     | Unit tests           |
| `composer test:func`     | Functional tests     |
| `composer test:intg`     | Integration tests    |
| `composer test:web`      | Acceptance API tests |
| `composer test:cli`      | Acceptance CLI tests |
| `composer test:all`      | All tests            |
| `composer test:coverage` | Coverage report      |
| `composer in`            | Mutation testing     |

---

## Development Priority

When creating a new feature, follow the Testing Trophy order.

1. **Static Analysis** — ensure `scan:php` and `scan:depend` pass
2. **Integration Tests** — write tests for Handler with test database and fixtures
3. **Unit Tests** — only if there's complex isolated logic
4. **End-to-End Tests** — only for critical scenarios
5. **Mutation Tests** — verify `composer in` passes

Don't aim for 100% unit test coverage. Integration tests provide more confidence with less maintenance cost.

---

## Test Examples

### Integration Test for Handler (Main Type)

Tests use a separate test database with fixtures. Each test runs in a transaction that is rolled back after completion.

```php
final class CreatePlayHandlerCest
{
    public function testCreatesPlaySuccessfully(IntegrationTester $I): void
    {
        // Arrange
        $game = GameFactory::create();
        $I->haveInDatabase($game);

        $command = new CreatePlayCommand(
            gameId: $game->id(),
            date: new DateTimeImmutable('2025-01-15'),
        );

        // Act
        $playId = $I->handleCommand($command);

        // Assert
        $I->assertNotNull($playId);
        $I->seeInDatabase('plays', ['id' => (string) $playId]);
    }

    public function testFailsWhenGameNotFound(IntegrationTester $I): void
    {
        $command = new CreatePlayCommand(
            gameId: GameId::generate(),
            date: new DateTimeImmutable(),
        );

        $I->expectThrowable(GameNotFoundException::class, function () use ($I, $command) {
            $I->handleCommand($command);
        });
    }
}
```

### Integration Test for Repository

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

### Unit Test for Value Object (Codeception Style)

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

---

## BDD Testing (Cli & Web)

### Architecture

```
Feature File (*.feature)
    ↓ Gherkin syntax
Step Definition (*Steps.php)
    ↓ thin layer, delegates to traits
Trait (*Trait.php)
    ↓ actual implementation, organized by data model
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
