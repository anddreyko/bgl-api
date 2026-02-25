# ADR-015: Test Layer Convention

## Date: 2026-02-26

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

The Testing Trophy approach was adopted in ADR/docs but no clear rule mapped test suites to architecture layers.
The Functional suite mixed real-DB tests with pure-stub tests. Documentation described Functional as "InMemory
without DB" while actual tests used Doctrine + PostgreSQL. AI agents consistently placed tests in wrong suites.

A convention is needed so that every test type has a single, unambiguous placement rule tied to the Clean Architecture
layer it exercises.

---

### Considered Options

#### Option 1: Classic Pyramid (Unit-Heavy)

Maximize unit tests, minimize integration tests.

**Pros:**

- Fast test suite
- Well-known pattern

**Cons:**

- Contradicts Testing Trophy strategy already adopted
- Unit-heavy approach couples tests to implementation details
- Misses integration bugs that are the primary failure mode in this codebase

#### Option 2: Arbitrary Grouping (Status Quo)

Keep current placement without strict rules. Developers decide per test.

**Pros:**

- No migration effort

**Cons:**

- No clear rules, ongoing confusion
- Agents and developers place tests inconsistently
- Documentation does not match reality

#### Option 3: Layer-Based Mapping

Each test suite maps to exactly one architecture layer.

**Pros:**

- Unambiguous placement rule
- Matches Clean Architecture layers already in use
- Fast functional tests (no DB) once InMemory repos are in place

**Cons:**

- Migration effort for existing tests (incremental, separate task)
- Requires InMemory implementations for each domain repository interface

---

### Decision

**Decision:** Option 3 -- Layer-based mapping.

**Layer Mapping:**

| Suite       | Layer              | What to Test                          | DI Container    | Backing Services |
|-------------|--------------------|---------------------------------------|-----------------|------------------|
| Unit        | Domain + Core pure | Invariants, validation, method logic  | No              | No               |
| Functional  | Application        | Handlers, use cases, state changes    | Yes (InMemory)  | No               |
| Integration | Infrastructure     | Repos, adapters, contract compliance  | Yes (real)      | Yes              |
| Web / Cli   | Presentation       | Happy paths, access control           | N/A (HTTP/CLI)  | Yes              |

**Rules per suite:**

- **Unit** -- Zero `Stub::makeEmpty()`. If a class needs stubs to test, it belongs in Functional, not Unit. Only
  pure classes: Value Objects, Entities (invariant logic), Exceptions, Collections.
- **Functional** -- DI container with InMemory/Fake bindings. Handler receives InMemory repos, FakeConfirmer,
  NullTransactor. Tests verify system state after executing a use case, not internal calls.
- **Integration** -- Real DI (`APP_ENV=test`), real database. Contract test pattern: abstract base class defines
  test methods, concrete class provides implementation via factory method.
- **Web / Cli** -- Only happy-path scenarios + access checks (authenticated/unauthenticated, roles). Edge cases
  and error paths are covered by Functional and Unit.
- **Core classes** -- Self-contained pure classes (VOs, exceptions) go to Unit. Classes requiring DI go to the
  suite matching their consumer layer.

**Test doubles strategy:**

- `Stub::makeEmpty()` is phased out. Replace with InMemory/Fake implementations via DI.
- InMemory repos live in `src/Infrastructure/Persistence/InMemory/` -- they implement domain interfaces with
  simple array storage.
- Fake/Null implementations for non-repo services live in `tests/Support/Dummy/` (FakeConfirmer, FakeTokenIssuer,
  NullTransactor).
- DI replacement in Functional tests uses PHP-DI `Container::set()` at runtime, managed by a Codeception module.

**Testing Trophy effort distribution (unchanged):**

1. Static Analysis -- base (catches whole class of errors without writing tests)
2. Functional + Integration -- **main effort** (widest part of trophy)
3. Unit -- only for complex pure logic
4. Web / Cli -- few critical paths

---

### Consequences

**Positive:**

- Clear, unambiguous placement rules for every test
- Fast functional tests once InMemory repos are available (no DB round-trips)
- Consistent behavior across human developers and AI agents
- Documentation matches actual conventions

**Negative/Risks:**

- Migration effort for existing tests that are in the wrong suite (incremental, separate task)
- Need to create InMemory implementations for each domain repository interface

### Notes

- Existing tests keep working until migrated; migration is incremental and tracked as a separate task
- Single source of truth for the full convention: `docs/02-onboarding/04-testing.md`
- This ADR records the decision; the testing guide contains the detailed how-to
