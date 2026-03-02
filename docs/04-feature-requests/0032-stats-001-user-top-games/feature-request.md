# STATS-001: Annual Report (Top Games + Summary)

## Summary

GET /v1/stats/annual-report endpoint returning user's annual gaming statistics:
top games by play count and summary counters (total plays, unique games, new games,
unique players, total hours, play days). SQL aggregation on-the-fly against
plays_session and plays_player tables.

## Requirements

1. GET /v1/stats/annual-report with auth required
2. Parameters: `year` (required, integer), `limit` (default 10, max 50)
3. Response contains two blocks:
   - `summary`: total_plays, unique_games, new_games, unique_players, total_hours, play_days
   - `top_games`: list of {game_id, game_name, play_count, total_duration_seconds, last_played_at}
4. Only count Published sessions (exclude Draft, Deleted)
5. Only count sessions with non-null game_id for top_games and unique/new games
6. `new_games` = games played for the first time in the requested year
7. `unique_players` = distinct co-players from plays_player in that year
8. `play_days` = distinct calendar days with at least one session
9. `total_hours` = sum of (finished_at - started_at) in seconds, null durations excluded

## Technical Approach

- **SQL on-the-fly** -- no denormalized tables, no extra entities
- Domain: `StatsReader` port (interface) + `AnnualReport` / `GamePlayStats` VOs in `Domain/Stats/`
- Infrastructure: `DoctrineStatsReader` with raw SQL via DBAL Connection
- Handler: `Stats/GetAnnualReport` (Query + Handler + Result)
- Game names resolved via `Games::findByIds()` batch call
- Indexes on plays_session for performance

## Out of Scope

- Categories breakdown (STATS-005, depends on GAMES-004)
- Monthly granularity (STATS-006)
- Year-over-year comparison (STATS-008)
- Game nominations (STATS-007)
- Delete play decrement (depends on PLAYS-005)
