# STATS-005: Categories in Annual Report

## Summary

Add categories breakdown to the annual report endpoint. Shows percentage distribution
of game categories based on play count for the year. Depends on GAMES-004 (categories in Game entity).

## Requirements

1. Extend GET /v1/stats/annual-report response with `categories` block
2. Each category entry: {name, play_count, percentage}
3. Sorted by play_count DESC
4. Percentage = category_play_count / total_plays * 100
5. A game can belong to multiple categories -- each category counted independently
6. Parameter: `categories_limit` (default 12, max 30)

## Technical Notes

- Requires GAMES-004 completed (Game has categories)
- Extend StatsReader with `categoriesByYear(Uuid $userId, int $year, int $limit): list<CategoryStats>`
- New VO: `CategoryStats` in Domain/Stats/ (name, playCount, percentage)
- SQL: JOIN plays_session with games_game, unnest categories JSONB, GROUP BY category
- Extend GetAnnualReport handler and Result
