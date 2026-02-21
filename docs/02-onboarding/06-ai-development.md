# AI-Assisted Development Guide

> Feature development workflow for BoardGameLog

## Overview

This project uses structured AI-assisted development workflow. All feature work follows a consistent process
tracked in `docs/04-feature-requests/`.

**Key principle:** Implementation is tracked in task folders with sequential numbering.

---

## Feature Development Workflow

### 1. Initialize

Create a task folder in `docs/04-feature-requests/{NNNN}-{task-name}/` with:

- `feature-request.md` -- full specification (what and why)

Review for ambiguities across these categories:

1. Functional Scope & Behavior
2. Domain & Data Model
3. Interaction & UX Flow
4. Non-Functional Quality Attributes
5. Integration & External Dependencies
6. Edge Cases & Failure Handling
7. Constraints & Tradeoffs
8. Terminology & Consistency

### 2. Plan

Create implementation artifacts:

- `master-checklist.md` -- progress tracker with validation criteria
- `stage-N-*.md` files -- implementation stages ordered by Testing Trophy

Mark independent stages with `[P]` for parallel execution:

```
- [ ] Stage 1: Domain Entities
- [ ] Stage 2: Value Objects [P]
- [ ] Stage 3: Repository Contracts [P]
- [ ] Stage 4: Integration Tests (depends on 1-3)
```

Include requirements quality evaluation: completeness, clarity, consistency, measurability, scenario coverage.

### 3. Implement

For each stage:

1. Read stage file instructions
2. Implement code following project patterns
3. Run verification (`composer lp:run`, `composer ps:run`)
4. Update checklist

### 4. Verify & Commit

- Run `composer scan:all` (mandatory)
- Verify compliance with AGENTS.md rules (dependency law, file placement, testing trophy)
- Update documentation if structure changed
- Create `documentation.md` in the task folder

---

## Consistency Checking

Cross-artifact validation between feature-request.md, master-checklist.md, and stage files:

- Coverage gaps (requirements without stages)
- Orphan stages (stages without requirements)
- Terminology drift
- AGENTS.md compliance

Severity levels: CRITICAL (blocks), HIGH, MEDIUM, LOW.

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
│   ├── master-checklist.md
│   ├── stage-1-*.md
│   └── documentation.md
│
└── ...
```

**Folder naming:** `{NNNN}-{task-id-lowercase}`

- NNNN = sequential number (0001, 0002, ...)
- task-id = task identifier (auth-001, games-002, ...)

---

## Quality Gates

| Stage                  | Commands run                               |
|------------------------|--------------------------------------------|
| After each file change | `composer lp:run` (lint)                   |
| After stage complete   | `composer ps:run` (psalm)                  |
| Before commit          | `composer scan:all` (full validation)      |
| For tests              | `composer test:intg`, `composer test:unit` |

**Never run vendor/bin directly** -- always through composer.

---

## Best Practices

### Do:

- Follow the plan -- stages are ordered by Testing Trophy
- Run verification after each change
- Commit checkpoints after each stage
- Capture important discoveries in project documentation

### Don't:

- Skip `composer scan:all` before push
- Implement without reading stage file first
- Mark checklist items done before verification passes
- Forget to update documentation on structural changes

---

*For technical details see `AGENTS.md` and `05-workflow.md`.*
