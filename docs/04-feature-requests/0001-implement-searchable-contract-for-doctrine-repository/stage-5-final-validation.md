# Stage 5: Final Validation and Cleanup

## Stage Overview

### What This Stage Accomplishes

This stage performs the final validation of the entire feature implementation. It ensures all quality checks pass, the
code meets project standards, and the implementation is production-ready. This includes running the mandatory
`make scan` command, verifying architecture compliance, and performing a code review for simplification opportunities.

### Why It Needs to Be Done at This Point

Final validation should be the last step before considering the feature complete. All previous stages have built and
tested the implementation; this stage confirms everything meets the project's quality standards.
Run `make scan` is **mandatory before push**.

### Dependencies

- All previous stages (1-4) completed
- All tests passing

---

## Implementation Steps

### Step 1: Run Full Quality Check Suite

Execute the mandatory scan command:

```bash
make scan
```

This command runs all validation tools including linting, static analysis, code style checks, and more. All checks must
pass.

### Step 2: Fix Any Psalm Errors

If Psalm reports errors, address them:

**Common Psalm issues:**
- Missing type declarations
- Incorrect return types
- Unsafe property access
- Generic type issues

Run Psalm independently to see detailed output:

```bash
make ps
```

### Step 3: Run Architecture Tests

Verify the implementation follows the dependency diagram:

```bash
make dt
```

This ensures:

- DoctrineFilter (Infrastructure) only depends on Core
- No circular dependencies introduced
- Layer boundaries are respected

### Step 4: Code Review for Simplification

Review the implementation for:

**Unnecessary complexity:**
- Can any methods be simplified?
- Are there redundant checks?
- Is the code readable?

**Code style consistency:**
- Follow PSR-12
- Use meaningful variable names
- Proper documentation

### Step 5: Run Final Test Suites

Run all test types in order as per Testing Trophy:

```bash
# Lint check
make lp

# Static analysis
make ps

# Integration tests
make t-intg

# Architecture tests
make dt
```

### Step 6: Final Validation

Run the complete scan one more time:

```bash
make scan
```

This confirms everything is ready for commit and push.

---

## Code References

### Project Quality Commands

| Command     | Purpose                                         |
|-------------|-------------------------------------------------|
| `make scan` | **MANDATORY before push** - runs all validation |
| `make ps`   | Deep type analysis with Psalm                   |
| `make dt`   | Architecture/dependency tests                   |

### Architecture Dependency Diagram

DoctrineFilter is in Infrastructure layer, which:

- CAN depend on: Core
- CANNOT depend on: Application, Presentation, Domain (for pure infrastructure)

---

## Files to Create/Modify

No new files in this stage. This stage is about validation.

### Potential Modifications (If Issues Found)

1. **DoctrineFilter.php** - Fix Psalm errors, improve documentation
2. **DoctrineRepository.php** - Fix any issues found during review

---

## Completion Criteria

### How to Verify This Stage is Done

1. **`make scan` passes completely:**
   ```bash
   make scan
   ```
   All checks must pass with no errors.

2. **Architecture tests pass:**
   ```bash
   make dt
   ```
   No deptrac violations.

3. **All tests pass:**
   ```bash
   make t-intg
   ```

4. **Code reviewed:**
    - Implementation follows project conventions
    - Code is readable and maintainable

### Commands to Run (In Order)

```bash
# 1. Lint check
make lp

# 2. Static analysis
make ps

# 3. Integration tests
make t-intg

# 4. Architecture tests
make dt

# 5. Final comprehensive check (MANDATORY)
make scan
```

### Expected Outcomes

- All quality checks pass
- No Psalm errors at level 1
- Architecture compliance verified
- Code is clean and maintainable
- Ready for commit and push

---

## Potential Issues

### Common Issues at This Stage

**Psalm errors about generics:**
```
Template type TResult is not used
```
**Solution:** Ensure `@implements FilterVisitor<string|Composite|null>` annotation is present.

---

**Architecture violation:**
```
Infrastructure depends on Application
```
**Solution:** Remove any imports from Application layer in DoctrineFilter.

---

**Code style violations:**
```
PSR-12 violation in DoctrineFilter.php
```
**Solution:** Run `make cs` to auto-fix code style.

### Final Checklist

Before marking the feature complete, verify:

- [ ] `make scan` passes completely
- [ ] All integration tests pass
- [ ] Architecture tests pass
- [ ] Code follows PSR-12
- [ ] All methods have `#[\Override]` where applicable
- [ ] PHPDoc annotations are complete
- [ ] No TODO comments left (except intentional)

### After Completion

Once all criteria are met:

1. **Commit the changes:**
   ```bash
   git add -A
   git commit -m "feat: implement DoctrineFilter for Searchable contract"
   ```

2. **Run final check:**
   ```bash
   make scan
   ```

3. **Push the changes:**
   ```bash
   git push
   ```

The feature is now complete and deployed.
