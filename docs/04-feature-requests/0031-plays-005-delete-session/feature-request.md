# PLAYS-005: Delete Session

## Summary

Soft-delete play sessions via existing UpdatePlay endpoint. Add `status` field to UpdatePlay Command
accepting `deleted` value. ListPlays must filter out deleted sessions by default.

## Requirements

1. DELETE /v1/plays/sessions/{id} endpoint with auth required
2. Add `Play::delete()` domain method (Draft/Published -> Deleted transition)
3. New DeletePlay handler (Command + Handler + Result)
4. ListPlays Handler: exclude Deleted sessions from results

## Out of Scope

- Separate "deleted sessions list" endpoint (can be added later)
- Hard delete / permanent removal
- Undo delete / restore
