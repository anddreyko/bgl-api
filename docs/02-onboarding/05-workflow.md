# Development Workflow

This document describes the complete development workflow: from creating a branch to merging a pull request.

For AI-assisted development details, see `06-ai-development.md`.

---

## Git Branching

Create branches from `develop` with a type prefix:

| Prefix      | Purpose                                       | Example                        |
|-------------|-----------------------------------------------|--------------------------------|
| `feat-`     | New business feature                          | `feat-add-auth-passkey-method` |
| `fix-`      | Bug fix                                       | `fix-play-date-bug`            |
| `refactor-` | Code refactoring                              | `refactor-repository`          |
| `test-`     | Test improvements                             | `test-plays-integration`       |
| `docs-`     | Documentation                                 | `docs-update-readme`           |
| `chore-`    | Maintenance or preparing core or integrations | `chore-update-dependencies`    |

---

## Commit Messages

Use **Conventional Commits** format:

```
<type>(<scope>): <description>
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
chore(plays): contract component validation
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
- Title only (no body/description)

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
composer lp:run    # Syntax check
composer ps:run    # Static analysis
```

Both must pass before committing.

### 4. Before Push

Run full validation:

```bash
composer scan:all  # MANDATORY - full check
```

This runs: lint, psalm, pdepend, deptrac, composer check, all tests, and mutation testing.

**Do not push if `composer scan:all` fails.**

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
1. Static Analysis    composer lp:run, composer ps:run, composer pd:check, composer dt:run
         |
2. Integration Tests  composer test:intg, composer test:func  <- MAIN FOCUS
         |
3. Unit Tests         composer test:unit (complex logic only)
         |
4. Acceptance Tests   composer test:web, composer test:cli
         |
5. Mutation Testing   composer in:ps (runs automatically as part of test:all)
         |
6. Benchmarks         composer bm:run (performance-sensitive changes)
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

- [ ] `composer lp:run` passes (syntax)
- [ ] `composer ps:run` passes (static analysis)
- [ ] `composer pd:check` passes (complexity: CCN <= 8, NPath <= 100, LOC <= 40, WMC <= 50)
- [ ] `composer dt:run` passes (architecture)
- [ ] `composer scan:all` passes (full validation incl. mutation testing)
- [ ] Integration tests written for new code
- [ ] `composer in:ps` passes (mutation testing, included in scan:all)
- [ ] `composer bm:check` passes (if performance-sensitive changes, optional)
- [ ] Commits follow Conventional Commits format
- [ ] No emojis in code, comments, or commits

---

## Quick Reference

| Stage         | Command             | Required          |
|---------------|---------------------|-------------------|
| Before commit | `composer lp:run`   | Yes               |
| Before commit | `composer ps:run`   | Yes               |
| Before push   | `composer scan:all` | **MANDATORY**     |
| Before push   | `composer in:ps`    | Included in scan:all |
| Before push   | `composer bm:check` | If perf-sensitive |
| CI            | All checks          | Must pass         |

---

## Related Documents

- `02-tooling.md` -- Available commands
- `04-testing.md` -- Testing strategy and examples
- `06-ai-development.md` -- AI-assisted development with FR commands
- `07-troubleshooting.md` -- Common issues and solutions
- `AGENTS.md` -- Complete rules reference
