# Master Checklist: PLAYS-006 Decouple Status from Finalization

## Stage 1: Unit Tests (Red)

- [ ] Add test: `finalize()` from Draft sets finishedAt, status stays Draft
- [ ] Add test: `finalize()` from Published sets finishedAt, status stays Published
- [ ] Add test: `finalize()` from Deleted throws PlayDeletedException
- [ ] Update test: `testFinalizeThrowsWhenPlayIsNotDraft` -- remove (finalize now allowed from Published)
- [ ] Update test: `testUpdateWorksWhenPublished` -- finalize no longer sets Published, create play with Published status directly via constructor
- [ ] Add test: `update()` with status Draft -> Published
- [ ] Add test: `update()` with status Published -> Draft
- [ ] Add test: `update()` with status to Deleted throws
- [ ] Remove `changeVisibility()` method and its test (dead code -- no callers outside Play entity)
- [ ] Run unit tests -- confirm new tests FAIL (Red)

## Stage 2: Domain Implementation (Green)

- [ ] `Play::finalize()`: remove `$this->status = PlayStatus::Published`, replace Draft-only check with Deleted check
- [ ] `Play::update()`: add `?PlayStatus $status = null` parameter, apply status if provided, validate transitions (no Deleted)
- [ ] Remove `Play::changeVisibility()` (dead code)
- [ ] Remove `PlayNotDraftException` class (no longer thrown after changes)
- [ ] Run unit tests -- confirm all PASS (Green)

## Stage 3: Functional Tests (Red)

- [ ] Update `CloseSessionCest::testSuccessfulClose`: assert status stays Draft after finalize
- [ ] Add `UpdatePlayCest::testStatusChangeDraftToPublished`
- [ ] Add `UpdatePlayCest::testStatusChangePublishedToDraft`
- [ ] Add `UpdatePlayCest::testStatusChangeToDeletedThrows`
- [ ] Update `GetPlayCest::finalizeSession()`: add explicit `$play->update(...)` with Published status + `$em->flush()` after finalize where visibility tests need Published status
- [ ] Run functional tests -- confirm new tests FAIL

## Stage 4: Application + API Implementation (Green)

- [ ] `UpdatePlay\Command.php`: add `public ?string $status = null` field (nullable -- omitted means "don't change")
- [ ] `UpdatePlay\Handler.php`: pass `$command->status !== null ? PlayStatus::from($command->status) : null` to `$play->update()`
- [ ] `config/common/openapi/plays.php`: add `status` enum field (draft/published) to PUT requestBody, nullable
- [ ] Run functional tests -- confirm all PASS

## Stage 5: Quality Gates

- [ ] `make scan` passes clean
- [ ] All play-related test groups pass: `--group=plays`, `--group=close-session`, `--group=update-play`, `--group=get-play`, `--group=delete-play`

---

## Review Fixes Applied

1. **GetPlayCest::finalizeSession()** -- нужен `EntityManagerInterface` + `flush()` + явный publish через update (просто finalize больше не публикует)
2. **Command.php status** -- `?string $status = null` (nullable), а не `string $status = 'draft'` (дефолт 'draft' сломал бы PUT full-replace для published plays)
3. **testUpdateWorksWhenPublished** -- добавлен в Stage 1 как затронутый тест
4. **changeVisibility()** -- мертвый код, удаляется вместе с `PlayNotDraftException`
