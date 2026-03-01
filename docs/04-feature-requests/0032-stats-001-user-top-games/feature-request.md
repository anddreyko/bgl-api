# STATS-001: User's Top Games

## Summary

GET /v1/stats/top-games endpoint returning user's most played games ranked by play count.
SQL aggregation on plays_session table grouped by game_id.

## Requirements

1. GET /v1/stats/top-games with auth required
2. Parameters: `limit` (default 10, max 50)
3. Response: list of {game_id, game_name, play_count, last_played_at} sorted by play_count DESC
4. Only count Published sessions (exclude Draft, Deleted)
5. Only count sessions with non-null game_id

## Technical Notes

- Requires new Plays repository method with GROUP BY aggregation (not Searchable)
- Game name resolved via Games repository (batch findByIds)
- New handler: Stats/GetUserTopGames (Query + Handler + Result)
- New OpenAPI config file: config/common/openapi/stats.php
