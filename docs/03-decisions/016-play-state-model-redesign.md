# ADR-016: Play State Model Redesign

**Status:** Accepted
**Date:** 2026-03-09
**Context:** Play sessions mix lifecycle, publication, and visibility in a single PlayStatus enum

---

## Problem

Current `PlayStatus(Draft|Published|Deleted)` conflates three orthogonal concerns:

1. **Lifecycle** -- is the game currently being played or already finished?
2. **Publication** -- is this a draft or published session?
3. **Visibility** -- who can see this session?

After PLAYS-006 decoupled finalization from publication, the remaining model still forces artificial
coupling. Users cannot express "game in progress, visible to friends" or "finished game, private draft".

## Decision

Replace single `PlayStatus` enum with three orthogonal axes:

### 1. Lifecycle (enum PlayLifecycle)

| Value | Meaning | finishedAt |
|-------|---------|------------|
| `current` | Game in progress right now | irrelevant |
| `finished` | Game completed (date optional -- user may not remember) | optional |
| `deleted` | Soft-deleted, hidden everywhere, excluded from stats, restorable | irrelevant |

### 2. Visibility (enum Visibility -- existing, unchanged)

| Value | Meaning |
|-------|---------|
| `private` | Owner only (replaces Draft semantics) |
| `link` | Anyone with direct link |
| `participants` | Owner + linked participants (MATES-002) |
| `authenticated` | Any authenticated user |
| `public` | Everyone |

### 3. Draft/Published axis -- REMOVED

`Private` visibility fully replaces `Draft`. "Publishing" = changing visibility from `private` to
any other value. No separate publication status needed.

## Lifecycle Transitions

```
create()    --> Current
finalize()  --> Current --> Finished
delete()    --> Current/Finished --> Deleted
restore()   --> Deleted --> Finished (always; Current is not restored)
```

Key rules:
- `Current` is assigned only at creation via `create()`
- `finalize()` transitions Current --> Finished; finishedAt is optional (user may not know the date)
- `delete()` from any non-deleted state; Current flag is lost
- `restore()` always returns to Finished (the game was played, even if interrupted)
- `Deleted` blocks all mutations except `restore()`

## Visibility Rules

- Visibility is freely changeable in `Current` and `Finished` states
- Visibility is ignored for `Deleted` (always hidden)
- `Private` = owner-only access (equivalent to old Draft behavior)
- No restriction on combining any visibility with any lifecycle state
  (e.g., Current + Public = live game visible to everyone)

## Invariants

| Invariant | Enforcement |
|-----------|-------------|
| finishedAt > startedAt (when set) | Play::finalize(), Play::setFinishedAt() |
| Deleted blocks mutations | All mutating methods check lifecycle |
| Current only via create() | No transition back to Current after finalize/delete |
| restore() always to Finished | Play::restore() hardcodes Finished |
| No double-delete | Play::delete() throws on Deleted |

## Impact

- `PlayStatus` enum replaced by `PlayLifecycle` enum (current, finished, deleted)
- `Visibility` enum unchanged
- Draft/Published distinction removed from domain
- API: `status` field in responses replaced by `lifecycle`
- API: PUT `status` param replaced by `lifecycle` (or removed -- lifecycle managed via dedicated endpoints)
- Stats queries filter by `lifecycle != deleted` instead of `status != deleted`
- GetPlay access matrix simplified: check visibility directly, no Draft special case

## Consequences

- Simpler domain model (2 axes instead of 3)
- More flexible UX (any visibility at any lifecycle stage)
- Breaking API change (status -> lifecycle in responses)
- Migration needed for existing data (Draft->Private+Finished, Published->keep visibility+Finished)
