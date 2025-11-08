# Quick Start

Welcome to **BoardGameLog** — an API for tracking board game sessions.

---

## Technology Stack

| Component    | Version | Purpose              |
|--------------|---------|----------------------|
| PHP          | 8.4     | Primary language     |
| PostgreSQL   | 15.2    | Database             |
| Slim         | 4.x     | HTTP micro-framework |
| Doctrine ORM | 3.x     | Persistence layer    |
| Codeception  | 5.3     | Testing              |

---

## Initial Setup

### 1. Clone and Install

```bash
git clone git@github.com:your-org/boardgame-log.git
cd boardgame-log/api

# Copy environment configuration
cp .env.example .env

# Install dependencies
composer install

# Install development tools (vendor-bin)
composer bin all install
```

### 2. Run with Docker

```bash
make init          # Initialize Docker containers
make up            # Start containers
make migrate       # Apply DB migrations
```

### 3. Verify Installation

```bash
composer scan:all  # All code quality checks + tests
```

If everything passes — the environment is ready.

---

## Key Project Rules

### Architecture

The project follows **Clean Architecture**. Dependencies point inward — inner layers (`Core`, `Domain`) do not depend on
outer layers (`Infrastructure`, `Presentation`).

```
Infrastructure/Presentation → Application → Domain → Core
```

Violating this rule is a critical error. Automatically verified via `composer dt`.

### Data Access

Always use Repository interfaces from `Domain`. Never bypass them directly. Raw SQL is only allowed in repository
implementations in `Infrastructure/Persistence/`.

### Secrets

Secrets are stored only in `.env`. `.env` files are never committed to the repository.

---

## Useful Links

| Document                                           | Description                                |
|----------------------------------------------------|--------------------------------------------|
| [02-tooling.md](02-tooling.md)                     | Commands and development tools             |
| [03-structure.md](03-structure.md)                 | Project structure and implementation rules |
| [04-testing.md](04-testing.md)                     | Testing strategy and workflow              |
| [../01-project-overview/](../01-project-overview/) | Project vision, domain, glossary           |
| [../03-decisions/](../03-decisions/)               | Architectural decisions (ADR)              |

---

## Starting Work on a Feature

1. Review documentation in `docs/01-project-overview/` to understand the domain
2. Check `docs/04-feature-requests/` for task description
3. Create branch from `develop`: `git checkout -b feat-short-desc`
4. Follow layer rules from [03-structure.md](03-structure.md)
5. Before commit: `composer scan:style` (formats code)
6. Before push: `composer scan:all` (analysis + tests)
