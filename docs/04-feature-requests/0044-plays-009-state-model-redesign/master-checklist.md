# Master Checklist: PLAYS-009 Play State Model Redesign

**Beads:** bgl-564
**ADR:** 016-play-state-model-redesign

---

## API Design

Lifecycle is internal domain concept, NOT exposed in API.

| Method | Endpoint | Action | Lifecycle transition |
|--------|----------|--------|---------------------|
| POST | `/v1/plays/sessions` | Create | -> Current |
| GET | `/v1/plays/sessions/{id}` | View | (none) |
| GET | `/v1/plays/sessions` | List | (none) |
| PUT | `/v1/plays/sessions/{id}` | Full replace | (none) |
| PATCH | `/v1/plays/sessions/{id}` | Partial update; finalize if `finished_at` sent | Current -> Finished |
| DELETE | `/v1/plays/sessions/{id}` | Delete | Current/Finished -> Deleted |
| PATCH | `/v1/plays/sessions/{id}/restore` | Restore | Deleted -> Finished |

Response: no `status`/`lifecycle` field. Client infers state from context (deleted = 404, current = no finished_at after create, etc.)

---

## Stage 1: Domain -- Enum + Entity (Unit Tests Red -> Green)

### 1.1 Create PlayLifecycle enum

- [ ] Create `src/Domain/Plays/PlayLifecycle.php`: `current`, `finished`, `deleted`
- [ ] Keep `PlayStatus.php` temporarily (for migration compatibility)

### 1.2 Update Play entity

- [ ] Replace `PlayStatus $status` with `PlayLifecycle $lifecycle` in constructor
- [ ] `create()`: set `PlayLifecycle::Current` instead of `PlayStatus::Draft`
- [ ] `getStatus()` -> `getLifecycle(): PlayLifecycle`
- [ ] `delete()`: guard `lifecycle === Deleted`, set `PlayLifecycle::Deleted`
- [ ] `finalize()`: guard `lifecycle !== Current` -> throw (only Current -> Finished); finishedAt optional (nullable param)
- [ ] Add `restore()`: guard `lifecycle !== Deleted` -> throw; set `PlayLifecycle::Finished`
- [ ] `update()`: remove `?PlayStatus $status` param; guard Deleted; no lifecycle changes via update
- [ ] `replacePlayers()`: guard Deleted (unchanged logic, new enum)
- [ ] `addPlayer()`: guard Deleted

### 1.3 Exceptions

- [ ] Keep `PlayDeletedException` as-is
- [ ] Add `PlayNotCurrentException` -- thrown when finalize() called on non-Current play

### 1.4 Unit tests (Red -> Green)

- [ ] Update `tests/Unit/Domain/Plays/Entities/PlayCest.php`:
  - `create()` returns lifecycle=Current
  - `finalize()` from Current -> Finished (with finishedAt)
  - `finalize()` from Current -> Finished (without finishedAt, null)
  - `finalize()` from Finished -> throws PlayNotCurrentException
  - `finalize()` from Deleted -> throws PlayDeletedException
  - `finalize()` with finishedAt <= startedAt -> throws (when finishedAt provided)
  - `delete()` from Current -> Deleted
  - `delete()` from Finished -> Deleted
  - `delete()` from Deleted -> throws PlayDeletedException
  - `restore()` from Deleted -> Finished
  - `restore()` from Current -> throws
  - `restore()` from Finished -> throws
  - `update()` on Deleted -> throws PlayDeletedException
  - `update()` no longer accepts status param
  - `replacePlayers()` on Deleted -> throws
  - `addPlayer()` on Deleted -> throws
- [ ] Run `composer test:unit` -- all pass

### Validation

```bash
composer test:unit
```

---

## Stage 2: Infrastructure -- Mapping + Migration

### 2.1 Update Doctrine mapping

- [ ] `PlayMapping.php`: rename column `status` -> `lifecycle`, update enum class to `PlayLifecycle`

### 2.2 Generate migration

- [ ] `make migrate-gen` -- auto-generates diff
- [ ] Verify migration: renames column, converts values (`draft` -> `finished`, `published` -> `finished`, `deleted` -> `deleted`)
- [ ] Add data migration in same file: all existing Draft/Published -> Finished (no Current retroactively)

### 2.3 Run migration

- [ ] `make migrate` -- apply
- [ ] `make validate-schema` -- pass

### Validation

```bash
make migrate && make validate-schema
```

---

## Stage 3: Application -- Handlers (Functional Tests Red -> Green)

### 3.1 CreatePlay Handler

- [ ] Remove `status` from Result (lifecycle not exposed)
- [ ] `transformPlay()`: remove `getStatus()` usage

### 3.2 UpdatePlay Handler

- [ ] Remove `status` from Command
- [ ] Remove status transition logic from Handler
- [ ] Remove `status` from Result

### 3.3 FinalizePlay Handler

- [ ] finishedAt already optional in Command -- keep as-is
- [ ] Remove `status` from Result

### 3.4 DeletePlay Handler

- [ ] Update lifecycle check: `PlayLifecycle::Deleted` instead of `PlayStatus::Deleted`

### 3.5 GetPlay Handler

- [ ] `checkAccess()`: simplify -- no Draft/Published distinction:
  - Deleted -> always 404
  - Private -> owner only
  - Other visibility -> apply visibility rules (same for Current and Finished)
- [ ] Remove `status` from Result

### 3.6 ListPlays Handler

- [ ] Filter: `lifecycle != deleted` instead of `status != deleted`
- [ ] Remove Draft filter for viewing others (no Draft concept)
- [ ] Remove `status` from Result items

### 3.7 Add RestorePlay Handler (NEW)

- [ ] Create `Application/Handlers/Plays/RestorePlay/Command.php` (sessionId, userId)
- [ ] Create `Application/Handlers/Plays/RestorePlay/Handler.php`
  - Find play (including Deleted), check ownership, call `play->restore()`
- [ ] Create `Application/Handlers/Plays/RestorePlay/Result.php` (same shape as other play Results, no status)

### 3.8 Functional tests

- [ ] Update `OpenSessionCest.php`: remove status assertion from response
- [ ] Update `UpdatePlayCest.php`: remove status transition tests, remove status from response assertions
- [ ] Update `CloseSessionCest.php`: remove status assertion, verify finishedAt set
- [ ] Update `DeletePlayCest.php`: use PlayLifecycle references internally
- [ ] Update `GetPlayCest.php`: replace Draft/Published access logic with visibility-only checks
- [ ] Add `RestorePlayCest.php`: restore from Deleted->accessible, restore non-Deleted->error, access denied
- [ ] Run `composer test:func`

### Validation

```bash
composer test:func
```

---

## Stage 4: Presentation -- API + OpenAPI

### 4.1 OpenAPI config

- [ ] `config/common/openapi/plays.php`:
  - PUT request body: remove `status` field
  - Add `PATCH /v1/plays/sessions/{id}/restore` route (RestorePlay)
  - Response schemas (Play component): remove `status` field
  - PATCH summary: "Partial update / finalize play session"

### 4.2 OpenAPI validation

- [ ] `composer oa:run` -- pass

### Validation

```bash
composer oa:run
```

---

## Stage 5: Cleanup + Quality Gates

### 5.1 Remove old code

- [ ] Delete `src/Domain/Plays/PlayStatus.php` (after all references removed)
- [ ] Remove any remaining PlayStatus imports

### 5.2 Full quality gates

- [ ] `make scan` -- all pass
- [ ] `composer test:all` -- all pass

### Validation

```bash
make scan && composer test:all
```

---

## Parallelism

- Stage 1 and Stage 2 can run in parallel [P] (enum+entity vs mapping+migration)
- Stage 3 depends on Stage 1 + Stage 2
- Stage 4 depends on Stage 3
- Stage 5 depends on all
