# Troubleshooting

Common issues and their solutions when working with BoardGameLog API.

---

## Psalm

### General Approach

**Never use `@psalm-suppress`.** Always fix the underlying code. If unsure how to fix, ask a teammate.

When facing many errors at once, fix them in this order:

1. **Infrastructure** (extraFiles, ignoreFiles in `psalm.xml`) -- eliminates false positives
2. **Factory-Entity mismatches** (TooMany/TooFew/InvalidArgument)
3. **UndefinedMethod** (remove calls to nonexistent methods)
4. **Remaining typing errors** (Mixed*, Possibly*, Redundant*)
5. Commit each phase separately

### TooManyArguments / TooFewArguments / InvalidArgument

**Symptom:** Psalm reports argument count or type mismatch when creating an Entity via Factory.

**Cause:** Factory is out of sync with Entity constructor.

**Fix:** Read the Entity constructor and align Factory arguments (count, order, types). The Entity is the source of
truth -- always adapt the Factory, not the Entity.

### UndefinedMethod (setters on Entity)

**Symptom:** `UndefinedMethod` for a setter like `$entity->setName(...)`.

**Cause:** DDD Entities are immutable and have no setters.

**Fix:** Remove the setter call. Pass the value through the Entity constructor instead.

### MixedAssignment / MixedArgument / MixedMethodCall

**Symptom:** Psalm cannot infer the type from a collection, query result, or JSON decode.

**Fix:** Add an assertion or phpdoc annotation to narrow the type:

```php
// For objects from collections or DI containers
$service = $container->get(MyService::class);
assert($service instanceof MyService);

// For SQL query results
/** @var array{id: string, name: string} $row */
$row = $statement->fetchAssociative();
```

### PossiblyNullReference / PossiblyNullArgument

**Symptom:** Psalm warns that a value might be null.

**Fix:** Add a null-check or use null-coalescing:

```php
// Before
$user->name()->value;

// After (null-check)
if ($user === null) {
    throw new UserNotFoundException($id);
}
$user->name()->value;

// After (null-coalescing, where applicable)
$value = $result?->value ?? $default;
```

### RedundantCondition / DocblockTypeContradiction

**Symptom:** Psalm says a condition is always true/false.

**Fix:** Remove the redundant check. If Psalm is correct about the types, the code simplifies.

### PossiblyUndefinedVariable

**Symptom:** Variable might not be defined when used after a loop or condition.

**Fix:** Initialize the variable with a default value before the loop/condition:

```php
$result = null;
foreach ($items as $item) {
    if ($item->matches($criteria)) {
        $result = $item;
    }
}
```

### UndefinedClass / UndefinedConstant (false positive)

**Symptom:** Class or constant exists in the codebase but Psalm cannot find it.

**Cause:** The file is not in Psalm's scan scope.

**Fix:** Add the missing directory to `<projectFiles>` or `<extraFiles>` in `psalm.xml`:

```xml
<extraFiles>
    <directory name="path/to/missing/directory"/>
</extraFiles>
```

### NoInterfaceProperties

**Symptom:** Accessing a property on a variable typed as an interface.

**Fix:** Use `instanceof` to narrow the type instead of null-checking:

```php
// Before
if ($result === null) { ... }

// After
if (!$result instanceof ConcreteEntity) {
    throw new NotFoundException();
}
// Now Psalm knows the exact type
$result->property;
```

### Clearing Psalm Cache

If Psalm reports stale errors after refactoring:

```bash
composer ps:clean
composer ps:run
```

Cache is stored in `var/.psalm.cache`.

---

## Testing (Codeception)

### General Approach

All tests use **Codeception** with Cest classes and Tester objects. PHPUnit-style `extends TestCase` is not supported.
See `04-testing.md` for strategy and examples.

### "Class not found" or "Actor not found"

**Symptom:** Test runner fails with `Class UnitTester not found` or similar.

**Fix:** Rebuild generated support classes:

```bash
composer test:rebuild
```

This runs `test:clean` + `test:build`, regenerating Tester classes from suite configs.

### Tests Show as "Incomplete" (BDD)

**Symptom:** Feature scenarios run but all show as "incomplete" or "undefined".

**Cause:** Step definitions are not registered in the suite configuration.

**Fix:** Register steps in `tests/Cli.suite.yml` or `tests/Web.suite.yml` under `gherkin.contexts.tag`:

```yaml
gherkin:
  contexts:
    tag:
      my-tag:
        - Bgl\Tests\Support\Step\Web\MySteps
```

### Integration Tests Fail with Database Errors

**Symptom:** Integration tests fail with connection or schema errors.

**Fix (step by step):**

1. Ensure Docker containers are running:

```bash
make up
make wait-db
```

2. Apply schema updates (Integration suite uses `orm:schema-tool:update` automatically via `CliRunnerExtension`, but you
   can run it manually):

```bash
make migrate
```

3. Verify `.env` has correct database credentials for the test environment.

### Web (Acceptance) Tests Fail with Connection Refused

**Symptom:** `Connection refused` or `Failed to connect` on Web suite.

**Cause:** Web tests connect to the running application via HTTP (`APP_EXTERNAL_HOST:APP_EXTERNAL_PORT`).

**Fix:**

1. Ensure all Docker services are running and healthy:

```bash
make up
```

2. Verify the app responds:

```bash
curl http://localhost:${APP_EXTERNAL_PORT}/api/v1/ping
```

3. Check that `APP_EXTERNAL_HOST` and `APP_EXTERNAL_PORT` are set in `.env`.

### Tests Fail After Changing a Class Constructor

**Symptom:** Tests fail with argument errors after modifying an Entity or Value Object constructor.

**Fix:** Update all factories, fixtures, and test setups that instantiate the changed class. Search for usages:

```bash
composer ps:run   # Psalm will catch mismatches
```

### Tests Pass Locally but Fail in CI

**Fix (common causes):**

1. **Test order dependency.** Tests are shuffled (`shuffle: true` in `codeception.yml`). Run locally multiple times to
   reproduce.
2. **Missing environment variable.** CI has a separate `.env`. Ensure all required vars are set.
3. **Database state.** Integration tests rely on schema auto-update. Ensure migrations are applied in CI.

### Running a Single Test

```bash
# Single Cest file
composer test -- run Unit tests/Unit/Path/To/MyCest.php

# Single method
composer test -- run Unit tests/Unit/Path/To/MyCest.php:testMethodName

# Single BDD scenario by line
composer test -- run Web tests/Web/my.feature:42
```

### Clearing Test Cache

```bash
composer test:clean
```

Test output and cache are stored in `var/.tests/`.

---

## Related Documents

- `02-tooling.md` -- Available commands and tools
- `04-testing.md` -- Testing strategy and examples
- `05-workflow.md` -- Development workflow and pre-push checklist
