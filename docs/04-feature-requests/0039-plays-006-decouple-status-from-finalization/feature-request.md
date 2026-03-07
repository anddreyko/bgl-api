# Feature Request: Decouple Status from Finalization (PLAYS-006)

**Document Version:** 1.0
**Date:** 2026-03-07
**Status:** In Progress
**Priority:** P1
**Beads:** bgl-7qv

---

## 1. Feature Overview

### Description

Decouple play session status management from finalization. Currently `finalize()` simultaneously sets `finishedAt` AND
changes status to `Published`. New behavior: `finalize()` only sets `finishedAt`, user controls status (draft/published)
independently via the existing PUT update endpoint. Delete rules remain unchanged.

### Business Value

- Users control when a session becomes visible (published) independently from when it ends (finishedAt)
- More flexible workflow: user can close a session but keep it as draft for editing before publishing
- Aligns with real-world usage: finishing a game and sharing it are separate decisions

### Target Users

- Board game enthusiasts who want granular control over session visibility

---

## 2. Current Behavior

### Play::finalize()
- Validates status === Draft (throws PlayNotDraftException otherwise)
- Validates finishedAt > startedAt
- Sets status = Published AND finishedAt

### Play::update()
- Updates name, gameId, visibility
- Rejects if status === Deleted
- Does NOT accept status field

### GetPlay::checkAccess()
- Draft sessions: owner-only access regardless of visibility setting
- Published sessions: visibility rules apply (private, link, public, etc.)

---

## 3. New Behavior

### Play::finalize()
- Remove status change (only set finishedAt)
- Allow finalization from any non-deleted status (Draft OR Published)
- Keep finishedAt > startedAt validation

### Play::update()
- Add optional `status` parameter (draft/published)
- Transitions allowed: Draft -> Published, Published -> Draft
- Reject transitions to/from Deleted (use DELETE endpoint)

### GetPlay::checkAccess()
- Draft sessions remain owner-only (unchanged)
- Visibility rules apply only to Published sessions (unchanged)

### changeVisibility()
- Remove Draft-only restriction (allow changing visibility in any non-deleted status)

---

## 4. Changes Summary

| File | Change |
|------|--------|
| `src/Domain/Plays/Play.php` | `finalize()`: remove status change, allow from Published; `update()`: add status param; `changeVisibility()`: remove Draft-only check |
| `src/Application/Handlers/Plays/UpdatePlay/Command.php` | Add optional `status` field |
| `config/common/openapi/plays.php` | Add `status` to PUT requestBody schema |
| `tests/Unit/Domain/Plays/Entities/PlayCest.php` | Update finalize tests, add status transition tests |
| `tests/Functional/Plays/CloseSessionCest.php` | Update: finalize no longer changes status |
| `tests/Functional/Plays/UpdatePlayCest.php` | Add status change tests |
| `tests/Functional/Plays/GetPlayCest.php` | Update `finalizeSession()` helper (finalize no longer publishes) |

---

## 5. Testing Strategy

### Unit Tests (Play entity)
- finalize() from Draft: sets finishedAt, status stays Draft
- finalize() from Published: sets finishedAt, status stays Published
- finalize() from Deleted: throws PlayDeletedException
- update() with status draft->published: success
- update() with status published->draft: success
- update() with status to deleted: throws (use DELETE instead)
- changeVisibility() works for Published status (not just Draft)

### Functional Tests
- CloseSessionCest: finalize keeps Draft status
- UpdatePlayCest: status change via update
- GetPlayCest: adjust tests that rely on finalize setting Published

---

## 6. Acceptance Criteria

- [ ] `finalize()` only sets finishedAt, does not change status
- [ ] `finalize()` works from both Draft and Published status
- [ ] `update()` accepts optional status field (draft/published)
- [ ] `changeVisibility()` works for any non-deleted status
- [ ] PUT endpoint accepts `status` field in request body
- [ ] Delete rules unchanged
- [ ] All existing tests updated and passing
- [ ] `make scan` passes
