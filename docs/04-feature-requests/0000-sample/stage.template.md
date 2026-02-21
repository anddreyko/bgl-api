# Stage Template

> Stage {N} of {Total}
> Task: {TASK-ID}
> Estimated Time: ~{X}min

## Overview

### What This Stage Accomplishes

{Description of what will be done}

### Why Now

{Why this stage needs to happen at this point in the sequence}

### Dependencies

- Stage {N-1}: {Name} - must be complete

---

## Implementation Steps

### Step 1: {Action}

**Files to create/modify:**

- `src/Path/File.php` - {purpose}

**Implementation:**

```php
// Code structure or example
```

**Verify:**

```bash
composer lp:run
```

---

### Step 2: {Action}

**Files to create/modify:**

- `src/Path/File.php` - {purpose}

**Implementation:**
{Instructions}

**Verify:**

```bash
composer ps:run
```

---

## Code References

Read these files before implementing:

| File                   | Lines | What to Learn     |
|------------------------|-------|-------------------|
| `src/Path/Similar.php` | all   | Pattern to follow |
| `src/Path/Related.php` | 45-67 | Integration point |

---

## Files Summary

### Create

| File                   | Purpose     |
|------------------------|-------------|
| `src/Path/NewFile.php` | Description |

### Modify

| File                    | Changes      |
|-------------------------|--------------|
| `src/Path/Existing.php` | Add method X |

---

## Completion Criteria

### Checklist

- [ ] All files created per specification
- [ ] All files pass `composer lp:run`
- [ ] All files pass `composer ps:run`
- [ ] No Deptrac violations (`composer dt:run`)

### Expected Outcomes

- {Outcome 1}
- {Outcome 2}

### Tests to Run

```bash
# Specific tests for this stage
composer test:unit -- tests/Unit/Path/...
composer test:intg -- tests/Integration/Path/...
```

---

## Potential Issues

### Common Mistakes

1. **{Mistake}** - {How to avoid}
2. **{Mistake}** - {How to avoid}

### Edge Cases

- {Edge case to handle}

### Troubleshooting

- **If Psalm error X:** {Solution}
- **If test fails with Y:** {Solution}

---

## After Completion

1. Update `master-checklist.md`:
    - Mark all sub-tasks as [x]
    - Update Progress Log

2. Commit checkpoint (optional):
   ```bash
   git add .
   git commit -m "feat({scope}): {stage description}"
   ```

3. Next stage: `/fr/stage stage-{N+1}-{slug}.md`
