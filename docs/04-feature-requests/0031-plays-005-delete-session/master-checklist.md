# PLAYS-005: Delete Session -- Master Checklist

## Stage 1: Tests (Red)

- [ ] Unit test: Play.delete() from Draft -> Deleted
- [ ] Unit test: Play.delete() from Published -> Deleted
- [ ] Unit test: Play.delete() from Deleted throws PlayAlreadyDeletedException
- [ ] Functional test: DeletePlay handler succeeds for owner (Draft session)
- [ ] Functional test: DeletePlay handler succeeds for owner (Published session)
- [ ] Functional test: DeletePlay on already deleted throws PlayAlreadyDeletedException
- [ ] Functional test: DeletePlay by non-owner throws PlayAccessDeniedException
- [ ] Functional test: DeletePlay on non-existent session throws NotFoundException
- [ ] Functional test: ListPlays excludes deleted sessions from results
- [ ] Web test: DELETE /v1/plays/sessions/{id} returns 204
- [ ] Web test: DELETE /v1/plays/sessions/{id} without auth returns 401

## Stage 2: Domain

- [ ] PlayAlreadyDeletedException (extends DomainException, like other named exceptions)
- [ ] Play::delete() method -- Draft|Published -> Deleted, throws PlayAlreadyDeletedException if already Deleted

## Stage 3: Application

- [ ] DeletePlay/Command.php -- `Uuid $sessionId, Uuid $userId` (follows FinalizePlay pattern)
- [ ] DeletePlay/Handler.php -- find play, check owner (PlayAccessDeniedException), call play.delete()
- [ ] DeletePlay/Result.php -- empty object (HTTP 204 No Content)

## Stage 4: Config + Wiring

- [ ] OpenAPI plays.php: add `delete` method to `/v1/plays/sessions/{id}` with x-message, x-interceptors [AuthInterceptor], x-auth ['userId'], x-map ['id' => 'sessionId'], response 204 No Content
- [ ] bus.php: register DeletePlay\Command -> DeletePlay\Handler
- [ ] _serialise-mapping.php: empty array `[]` (no body for 204)
- [ ] ApiAction::doHandle(): support 204 -- if Result has httpStatus property or data is null, pass httpStatus to SuccessResponse
- [ ] ListPlays/Handler.php: add `Not(new Equals(new Field('status'), PlayStatus::Deleted->value))` to buildFilter()

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
