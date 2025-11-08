# Vendor-bin for Development Tools

## Date: 2024-01-20

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

In PHP projects, development tools (static analyzers, linters, formatters) often conflict with each other due to
incompatible dependencies. For example, Psalm and Rector may require different versions of the same library, making
simultaneous installation in one `composer.json` impossible.

**Challenges:**

- Psalm, PHP-CS-Fixer, Rector, Deptrac have overlapping dependencies
- Updating one tool may break another
- `composer.lock` becomes complex and conflicting
- Development tools clutter the main `vendor/` directory

**Requirements:**

- Isolated installation of each tool
- Ability to update tools independently
- No dependency conflicts
- Clean root `composer.json` for production dependencies

### Considered Options

#### Option 1: Everything in Root composer.json

All tools are installed as `require-dev` dependencies in the main `composer.json`.

**Pros:**

- Simple project structure
- One `composer install` installs everything
- Familiar to most developers

**Cons:**

- Dependency conflicts between tools
- Difficulty updating individual tools
- Bloated `vendor/` directory
- Risk of breakage on update

#### Option 2: Vendor-bin (bamarni/composer-bin-plugin)

Each tool is installed in a separate `vendor-bin/{tool}/` directory with its own `composer.json` and `composer.lock`.

**Pros:**

- Complete isolation of tools from each other
- Independent update of each tool
- No dependency conflicts
- Clean root `composer.json`

**Cons:**

- Additional project structure complexity
- Requires vendor-bin approach knowledge
- More files in repository
- More complex CI/CD configuration

#### Option 3: Global Tool Installation

Tools are installed globally via `composer global require`.

**Pros:**

- Don't clutter the project
- Available across all projects

**Cons:**

- Different versions on different developer machines
- Difficulty synchronizing versions across team
- CI/CD problems
- No project versioning possible

### Decision

**Decision:** Vendor-bin adopted (Option 2) using `bamarni/composer-bin-plugin`

**Reason for choice:**

1. **Isolation** — each tool lives in its own sandbox and doesn't conflict with others
2. **Versioning** — each tool has its own `composer.lock`, guaranteeing reproducibility
3. **Independent updates** — can update Psalm without touching Rector
4. **Cleanliness** — root `composer.json` contains only production dependencies

### Consequences

**Positive:**

- No dependency conflicts between tools
- Easy to add new tools without risk of breaking existing ones
- Each tool can be updated independently
- CI/CD works reproducibly thanks to separate lock files

**Negative/Risks:**

- New developers need vendor-bin structure explanation
- More composer.json and composer.lock files in repository
- More complex initial setup

### Notes

**Vendor-bin Structure:**

```
vendor-bin/
├── psalm/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
├── php-cs-fixer/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
├── rector/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
├── codeception/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
├── deptrac/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
└── infection/
    ├── composer.json
    ├── composer.lock
    └── vendor/
```

**Install All Tools:**

```bash
composer bin all install
```

**Install Specific Tool:**

```bash
composer bin psalm install
```

**Update Specific Tool:**

```bash
composer bin rector update
```

**Ways to Run Tools:**

Developers can use any convenient method:

1. **Composer scripts** (recommended) — commands defined in root `composer.json` scripts section:
   ```bash
   # Individual tools
   composer cs             # PHP-CS-Fixer
   composer rc             # Rector
   composer lp             # PHP Lint
   composer ps             # Psalm
   composer dt             # Deptrac

   # Check groups
   composer scan:style     # PHP-CS-Fixer + Rector (modifies code)
   composer scan:php       # Lint + Psalm
   composer scan:depend    # Deptrac + Composer dependencies
   composer scan:all       # scan:php + scan:depend + test:all (without scan:style)
   ```

2. **Makefile** — wrappers over composer commands:
   ```bash
   make ps
   make cs
   make scan
   ```

3. **Direct invocation** — via path to binary in vendor-bin:
   ```bash
   vendor-bin/psalm/vendor/bin/psalm
   ```

**Note:** The `scan:all` command intentionally excludes `scan:style` since it modifies code. Run `scan:style` separately
first, then `scan:all` for verification.

**Composer Scripts (composer.json):**

```json
{
    "scripts": {
        "cs": "vendor-bin/php-cs-fixer/vendor/bin/php-cs-fixer fix",
        "rc": "vendor-bin/rector/vendor/bin/rector process",
        "lp": "vendor-bin/phplint/vendor/bin/phplint",
        "ps": "vendor-bin/psalm/vendor/bin/psalm",
        "dt": "vendor-bin/deptrac/vendor/bin/deptrac analyse",
        "scan:style": ["@cs", "@rc"],
        "scan:php": ["@lp", "@ps"],
        "scan:depend": ["@dt"],
        "scan:all": ["@scan:php", "@scan:depend", "@test:all"]
    }
}
```

**Root composer.json:**

```json
{
    "require": {
        "php": "^8.4",
        "slim/slim": "^4.0",
        "doctrine/orm": "^3.0"
    },
    "require-dev": {
        "bamarni/composer-bin-plugin": "^1.8"
    },
    "extra": {
        "bamarni-bin": {
            "bin-links": false,
            "forward-command": true
        }
    }
}
```

**CI/CD Configuration:**

```yaml
# GitHub Actions example
- name: Install dependencies
  run: composer install --no-progress

- name: Install dev tools
  run: composer bin all install --no-progress

- name: Fix code style
  run: composer scan:style

- name: Run full check (analysis + tests)
  run: composer scan:all
```

**Tools in vendor-bin:**

| Directory       | Tool         | Purpose                           |
|-----------------|--------------|-----------------------------------|
| `psalm/`        | Psalm        | Static type analysis              |
| `php-cs-fixer/` | PHP-CS-Fixer | Auto code style fixing            |
| `rector/`       | Rector       | Automated refactoring             |
| `codeception/`  | Codeception  | Testing framework                 |
| `deptrac/`      | Deptrac      | Architectural dependency checking |
| `infection/`    | Infection    | Mutation testing                  |

**References:**

- [bamarni/composer-bin-plugin](https://github.com/bamarni/composer-bin-plugin)
- [Why vendor-bin](https://blog.wyrihaximus.net/2015/06/composer-bin-plugin/)
