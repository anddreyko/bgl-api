# AI-Assisted Development Guide

> How to use `/fr/*` commands for feature development with Claude Code

## Overview

This project uses structured AI-assisted development workflow. All feature work follows a consistent process tracked in
`docs/04-feature-requests/`.

**Key principle:** Tasks come from BACKLOG.md, implementation is tracked in task folders with sequential numbering.

---

## Quick Start

### Start working on a task from backlog:

```bash
# 1. Initialize task
/fr/init AUTH-001

# 2. Create implementation plan
/fr/plan

# 3. Implement stages one by one
/fr/stage

# 4. When all stages complete
/fr/commit
```

### Add a new task to backlog:

```bash
/fr/add AUTH 1 Password Reset: Allow users to reset password via email
```

---

## Command Reference

### Core Workflow Commands

| Command              | Purpose                                  | When to use               |
|----------------------|------------------------------------------|---------------------------|
| `/fr/init {TASK-ID}` | Initialize task from BACKLOG             | Starting new feature      |
| `/fr/plan`           | Create implementation plan               | After init, before coding |
| `/fr/stage [file]`   | Implement stage (auto-detect if no file) | During development        |
| `/fr/commit`         | Finalize and document                    | After all stages complete |

### Support Commands

| Command             | Purpose                           | When to use                     |
|---------------------|-----------------------------------|---------------------------------|
| `/fr/status`        | Check progress                    | Anytime                         |
| `/fr/update {text}` | Update spec with new requirements | Requirements changed            |
| `/fr/fix {issue}`   | Fix problems                      | When tests fail or errors occur |
| `/fr/reset {stage}` | Rollback stage                    | When need to start over         |
| `/fr/note {text}`   | Add to AGENTS.md                  | Discovered important rule       |

### Backlog Commands

| Command                | Purpose           | When to use                 |
|------------------------|-------------------|-----------------------------|
| `/fr/add {task}`       | Quick add task    | New feature idea            |
| `/fr/backlog add`      | Full format add   | Detailed task specification |
| `/fr/backlog reorder`  | Change priorities | Dependencies changed        |
| `/fr/backlog progress` | Recalculate MVP % | After completing tasks      |

---

## Detailed Workflows

### Workflow 1: Implement Feature from Backlog

This is the standard workflow for planned features.

```
BACKLOG.md → /fr/init → /fr/plan → /fr/stage (repeat) → /fr/commit
```

**Step 1: Initialize**

```bash
/fr/init AUTH-001
```

Creates:

- `docs/04-feature-requests/0002-auth-001-user-registration/`
- `feature-request.md` with full specification

**Step 2: Plan (Plan Mode)**

```bash
/fr/plan
```

Creates:

- `master-checklist.md` — overall progress tracker
- `stage-1-domain-entities.md`, `stage-2-repositories.md`, etc.

**Step 3: Implement stages**

```bash
# Start first stage
/fr/stage stage-1-domain-entities.md

# Continue with next (auto-detect)
/fr/stage

# Or specify explicitly
/fr/stage stage-3-handlers.md
```

Each stage:

1. Reads stage file instructions
2. Implements code following patterns
3. Runs verification (`composer lp:run`, `composer ps:run`)
4. Updates checklist

**Step 4: Commit**

```bash
/fr/commit
```

- Runs `composer scan:all` (mandatory)
- Generates commit message
- Updates CHANGELOG.md
- Creates documentation.md

---

### Workflow 2: Add New Task to Backlog

**Quick add (one line):**

```bash
/fr/add PLAYS 2 Photo Attachments: Allow adding photos to play sessions
```

Format: `{CONTEXT} {phase} {title}: {description}`

**Contexts:** AUTH, GAMES, PLAYS, STATS, MATES, SYNC, CORE, INFRA, API

**Phases:**

- 0 = Foundation (contracts, infrastructure)
- 1 = MVP (core features)
- 2 = Expansion (nice-to-have)
- 3 = Scaling (performance)

**With explicit priority:**

```bash
/fr/add CORE 0 API Contract: REST API specification, this should be done first
```

**Full format (interactive):**

```bash
/fr/backlog add
```

Then provide structured input with all fields.

---

### Workflow 3: Check and Fix Issues

**Check current status:**

```bash
/fr/status
```

Shows:

- Current task and progress
- Remaining stages
- Health check (lint, psalm)
- Git status

**Fix implementation issue:**

```bash
/fr/fix Psalm error: Property $id has no type
```

**Rollback stage:**

```bash
/fr/reset 2
```

Reverts stage 2 changes, updates checklist.

---

### Workflow 4: Manage Priorities

**Recalculate progress after completing tasks:**

```bash
/fr/backlog progress
```

Updates PROJECT-STATUS.md with:

- Completed tasks count
- MVP percentage
- Progress bar

**Reorder tasks:**

```bash
/fr/backlog reorder
```

Then describe changes:

- "Move GAMES-002 before GAMES-001"
- "AUTH-003 depends on AUTH-002, fix order"

Priority rules:

1. Explicit instruction → follow exactly
2. No instruction → auto by dependencies
3. Unclear dependencies → Claude asks

---

## File Structure

```
docs/04-feature-requests/
├── 0000-sample/                    # Templates
│   ├── feature-request.template.md
│   ├── master-checklist.template.md
│   ├── stage.template.md
│   └── documentation.template.md
│
├── 0001-searchable-contract/       # Completed task
│   ├── feature-request.md
│   ├── master-checklist.md         # Shows 5/5 stages done
│   ├── stage-1-*.md
│   └── documentation.md
│
├── 0002-auth-001/                  # Current task
│   ├── feature-request.md
│   ├── master-checklist.md         # Shows 2/6 stages done
│   └── stage-*.md
│
├── BACKLOG.md                      # All planned tasks
└── PROJECT-STATUS.md               # Current focus, progress
```

**Folder naming:** `{NNNN}-{task-id-lowercase}`

- NNNN = sequential number (0001, 0002, ...)
- task-id = from BACKLOG (auth-001, games-002, ...)

---

## Integration with Composer Commands

All `/fr/*` commands use `composer` under the hood:

| Stage                  | Commands run                        |
|------------------------|-------------------------------------|
| After each file change | `composer lp:run` (lint)            |
| After stage complete   | `composer ps:run` (psalm)           |
| Before commit          | `composer scan:all` (full validation) |
| For tests              | `composer test:intg`, `composer test:unit` |

**Never run vendor/bin directly** — always through composer.

---

## Best Practices

### Do:

- Follow the plan — stages are ordered by Testing Trophy
- Run verification after each change
- Commit checkpoints after each stage
- Use `/fr/note` to capture important discoveries
- Reset and start over if stuck (it's cheap with git)

### Don't:

- Skip `composer scan:all` before push
- Implement without reading stage file first
- Mark checklist items done before verification passes
- Forget to update documentation on structural changes

---

## Troubleshooting

### "Stage fails verification"

```bash
/fr/fix {paste error message}
```

### "Need to undo last stage"

```bash
/fr/reset {stage number}
```

### "Lost track of progress"

```bash
/fr/status
```

### "Unclear what to do next"

```bash
/fr/stage
```

### "Want to add a quick note"

```bash
/fr/note Never use deprecated PasswordEncoder
```

---

## Examples

### Example 1: Complete feature implementation

```bash
# Day 1: Start feature
/fr/init AUTH-001
/fr/plan
/fr/stage stage-1-domain-entities.md
git commit -m "feat(auth): add User entity and value objects"

# Day 2: Continue
/fr/status                              # See where we left off
/fr/stage                          # Continue stage 2
/fr/stage                          # Continue stage 3

# Day 3: Finish
/fr/stage                          # Stage 4
/fr/stage                          # Stage 5 (tests)
/fr/commit                              # Finalize
git push origin feature/auth-001
```

### Example 2: Quick bug fix task

```bash
# Add bug as task
/fr/add AUTH 1 Fix Token Expiry: JWT tokens expire incorrectly

# Initialize and plan
/fr/init AUTH-005
/fr/plan

# Single stage fix
/fr/stage
/fr/commit
```

### Example 3: Explore before implementing

```bash
# Check current state
/fr/status

# Read about the task
cat docs/04-feature-requests/BACKLOG.md | grep -A 50 "AUTH-001"

# Initialize when ready
/fr/init AUTH-001
```

---

## Command Cheat Sheet

```bash
# === MAIN WORKFLOW ===
/fr/init {TASK-ID}     # Start task
/fr/plan               # Create stages
/fr/stage              # Next stage (or /fr/stage file.md)
/fr/commit             # Finish

# === HELPERS ===
/fr/status             # Where am I?
/fr/update {text}      # Add new requirements
/fr/fix {error}        # Fix issue
/fr/reset {N}          # Undo stage N
/fr/note {text}        # Remember this

# === BACKLOG ===
/fr/add {CTX} {phase} {title}: {desc}
/fr/backlog progress   # Update MVP %
/fr/backlog reorder    # Change order
```

---

*For technical details see `AGENTS.md` section 11.*
