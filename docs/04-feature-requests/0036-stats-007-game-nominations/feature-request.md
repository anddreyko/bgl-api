# STATS-007: Game Nominations

## Summary

Automated game nominations per user per year based on play statistics.
"Most Played 2024", "Longest Sessions 2024", etc. Displayed on game detail page
and in annual report. Depends on STATS-001.

## Requirements

1. Nomination types (MVP): most_played, longest_sessions, most_frequent_month
2. Nominations auto-generated from stats data (not user-editable in MVP)
3. GET /v1/stats/nominations?year=YYYY returns list of nominations for user
4. GET /v1/games/{id} extended with nominations for that game
5. Nominations recalculated on demand or via CLI command
6. Future: user-defined nominations, "Game of the Year" voting

## Technical Notes

- Write-model: `GameNomination` entity in Domain/Stats/
  - userId, gameId, year, type (enum), value (int -- play count or duration)
- Nomination logic: query StatsReader for top-1 per metric, create/update nominations
- CLI command: `stats:nominate [--year=YYYY] [--user=UUID]`
- Can be triggered after annual report generation
- No real-time updates -- batch process acceptable for MVP
