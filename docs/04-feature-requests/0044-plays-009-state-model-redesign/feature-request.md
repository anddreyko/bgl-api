# Feature Request: Play State Model Redesign (PLAYS-009)

**Document Version:** 1.0
**Date:** 2026-03-09
**Status:** Draft
**Priority:** P1
**ADR:** 016-play-state-model-redesign

---

## 1. Feature Overview

### Description

Replace `PlayStatus(Draft|Published|Deleted)` with orthogonal state model:
- **Lifecycle** (`PlayLifecycle`): `current | finished | deleted`
- **Visibility** (`Visibility`): `private | link | participants | authenticated | public` (existing enum, unchanged)

Draft/Published distinction removed. `Private` visibility replaces Draft semantics.

### Business Value

- "Current game" is a first-class concept (live session tracking)
- Users control visibility independently from lifecycle
- Simpler mental model: "is it happening?" + "who can see it?"

---

## 2. Current State

### PlayStatus enum
- `draft` -- created, not published
- `published` -- visible per visibility rules
- `deleted` -- soft-deleted

### Visibility enum (unchanged)
- `private`, `link`, `participants`, `authenticated`, `public`

### Behavior
- `create()` -> Draft
- `update(status: published)` -> Published
- `finalize()` -> sets finishedAt only (PLAYS-006)
- `delete()` -> Deleted (irreversible)

---

## 3. New State Model

### PlayLifecycle enum (replaces PlayStatus)

```php
enum PlayLifecycle: string
{
    case Current = 'current';
    case Finished = 'finished';
    case Deleted = 'deleted';
}
```

### Transitions

```
create()    -> Current
finalize()  -> Current -> Finished (finishedAt optional)
delete()    -> Current/Finished -> Deleted
restore()   -> Deleted -> Finished (always Finished, never Current)
```

### Rules

1. `Current` assigned only at `create()`; never restored
2. `finalize()`: Current -> Finished; finishedAt is optional (user may not remember the date)
3. `delete()`: from any non-Deleted state; if Current -- Current is lost
4. `restore()`: always to Finished
5. `Deleted`: all mutations blocked except `restore()`
6. Visibility freely changeable in Current and Finished
7. Visibility ignored for Deleted (always hidden)
8. `Private` visibility = owner-only (replaces Draft semantics)

### finishedAt

- Optional field, independent of lifecycle
- Can be null in any lifecycle state (Current, Finished, Deleted)
- If set: must be > startedAt
- Set via `finalize()` or `update()` -- user controls the value

---

## 4. Access Matrix (GetPlay)

| Lifecycle | Visibility | Owner | Other Auth | Anonymous |
|-----------|------------|-------|------------|-----------|
| Current | private | 200 | 404 | 404 |
| Current | link | 200 | 200 | 200 |
| Current | participants | 200 | TODO:MATES-002 | 404 |
| Current | authenticated | 200 | 200 | 401 |
| Current | public | 200 | 200 | 200 |
| Finished | (same as Current) | | | |
| Deleted | (any) | 404 | 404 | 404 |

No special Draft handling -- `private` visibility does the job.

---

## 5. API Changes

### Response: `status` -> `lifecycle`

Before: `{ "status": "draft" }`
After: `{ "lifecycle": "current" }`

### Endpoints

| Endpoint | Change |
|----------|--------|
| POST `/v1/plays/sessions` | Response: `lifecycle: current` instead of `status: draft` |
| PUT `/v1/plays/sessions/{id}` | Remove `status` param; add optional `lifecycle` param (only `finished` transition?) or keep lifecycle via dedicated endpoints |
| PATCH `/v1/plays/sessions/{id}` | finalize: sets lifecycle=finished (+ optional finishedAt) |
| DELETE `/v1/plays/sessions/{id}` | Sets lifecycle=deleted |
| POST `/v1/plays/sessions/{id}/restore` | NEW: restore from deleted to finished |

### ListPlays filters

- Replace `status != deleted` with `lifecycle != deleted`
- Add optional `lifecycle` filter (current/finished)

---

## 6. Data Migration

| Current state | New lifecycle | New visibility |
|---------------|--------------|----------------|
| Draft + Private | Finished | private |
| Draft + other visibility | Finished | keep |
| Published + any visibility | Finished | keep |
| Deleted | Deleted | keep |

All existing sessions become Finished (no way to know if game is "current" retroactively).

---

## 7. Domain Changes

| File | Change |
|------|--------|
| `Domain/Plays/PlayStatus.php` | Remove -> replace with `PlayLifecycle.php` |
| `Domain/Plays/PlayLifecycle.php` | NEW: `current`, `finished`, `deleted` |
| `Domain/Plays/Play.php` | Replace `status` with `lifecycle`; add `restore()`; update `finalize()`, `delete()`, `update()` |
| `Domain/Plays/PlayDeletedException.php` | Keep (used for Deleted state guard) |
| Handlers (all) | Replace PlayStatus references with PlayLifecycle |
| OpenAPI config | Update schemas, add restore endpoint |
| DB migration | Rename column `status` -> `lifecycle`, update values |
| Tests (all play-related) | Update for new enum and transitions |

---

## 8. Acceptance Criteria

- [ ] `PlayLifecycle` enum with `current`, `finished`, `deleted`
- [ ] `PlayStatus` enum removed
- [ ] `create()` sets lifecycle = Current
- [ ] `finalize()` transitions Current -> Finished, finishedAt optional
- [ ] `delete()` from Current/Finished -> Deleted
- [ ] `restore()` from Deleted -> Finished
- [ ] `restore()` never returns to Current
- [ ] Deleted blocks all mutations except restore()
- [ ] Visibility changeable in Current and Finished
- [ ] Private visibility = owner-only access
- [ ] API responses use `lifecycle` instead of `status`
- [ ] Restore endpoint works
- [ ] Data migration converts existing records
- [ ] ListPlays filters by lifecycle
- [ ] Stats exclude lifecycle=deleted
- [ ] All tests pass
- [ ] `make scan` passes
