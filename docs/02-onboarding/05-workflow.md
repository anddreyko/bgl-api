# Development Workflow

This document describes the complete development workflow: from creating a branch to merging a pull request.

---

## Git Branching

Create branches from `develop` with a type prefix:

| Prefix      | Purpose           | Example                      |
|-------------|-------------------|------------------------------|
| `feat-`     | New feature       | `feat-add-player-validation` |
| `fix-`      | Bug fix           | `fix-play-date-bug`          |
| `refactor-` | Code refactoring  | `refactor-repository`        |
| `test-`     | Test improvements | `test-plays-integration`     |
| `docs-`     | Documentation     | `docs-update-readme`         |
| `chore-`    | Maintenance       | `chore-update-dependencies`  |

---

## Commit Messages

Use **Conventional Commits** format:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:**

| Type       | Purpose                                  |
|------------|------------------------------------------|
| `feat`     | New feature                              |
| `fix`      | Bug fix                                  |
| `refactor` | Code change without behavior change      |
| `test`     | Adding or updating tests                 |
| `docs`     | Documentation only                       |
| `chore`    | Maintenance, dependencies                |
| `style`    | Code style (formatting, no logic change) |

**Examples:**

```
feat(plays): add player count validation
fix(auth): handle expired token correctly
refactor(domain): extract PlayerId value object
test(plays): add integration tests for repository
docs(readme): update installation instructions
```

**Rules:**

- Use imperative mood: "add" not "added"
- Lowercase first letter after colon
- No period at end
- Keep first line under 72 characters
- No emojis in commit messages

---

## Development Cycle

### 1. Before Starting

```bash
git checkout develop
git pull origin develop
git checkout -b feat-my-feature
```

### 2. During Development

Follow the Testing Trophy approach (see `04-testing.md`):

1. Write integration tests first
2. Implement the feature
3. Add unit tests only for complex logic
4. Run checks frequently

### 3. Before Commit

Run quick checks:

```bash
make lp    # Syntax check
make ps    # Static analysis
```

Both must pass before committing.

### 4. Before Push

Run full validation:

```bash
make scan  # MANDATORY - full check
```

This runs: lint, psalm, deptrac, composer check, and all tests.

**Do not push if `make scan` fails.**

### 5. Pull Request

Create PR to `develop` branch:

- All CI checks must pass
- Describe what changed and why
- Link related issues/tasks
- Request review if needed

---

## Testing Order (Trophy)

When implementing features, follow this order:

```
1. Static Analysis    make lp, make ps, make dt
         ↓
2. Integration Tests  make t-intg, make t-func  ← MAIN FOCUS
         ↓
3. Unit Tests         make t-unit (complex logic only)
         ↓
4. Acceptance Tests   make t-web, make t-cli
         ↓
5. Mutation Testing   make in
```

Integration tests are the primary source of confidence. Don't aim for 100% unit test coverage.

---

## TDD Guidelines

| Situation                 | Approach                     |
|---------------------------|------------------------------|
| New functionality         | Write tests first (TDD)      |
| Tests already exist       | Write tests first (TDD)      |
| Bug fix without tests     | Fix first, write tests after |
| Refactoring without tests | Write tests after            |

---

## Pre-Push Checklist

Before pushing your branch:

- [ ] `make lp` passes (syntax)
- [ ] `make ps` passes (static analysis)
- [ ] `make dt` passes (architecture)
- [ ] `make scan` passes (full validation)
- [ ] Integration tests written for new code
- [ ] Commits follow Conventional Commits format
- [ ] No emojis in code, comments, or commits

---

## Quick Reference

| Stage         | Command     | Required      |
|---------------|-------------|---------------|
| Before commit | `make lp`   | Yes           |
| Before commit | `make ps`   | Yes           |
| Before push   | `make scan` | **MANDATORY** |
| CI            | All checks  | Must pass     |

---

## Related Documents

- `02-tooling.md` — Available commands
- `04-testing.md` — Testing strategy and examples
- `AGENTS.md` — Complete rules reference
