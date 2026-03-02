# STATS-006: Monthly Reports

## Summary

GET /v1/stats/monthly-report endpoint returning user's gaming statistics for a specific month.
Same metrics as annual report but scoped to a single month. Depends on STATS-001.

## Requirements

1. GET /v1/stats/monthly-report with auth required
2. Parameters: `year` (required), `month` (required, 1-12), `limit` (default 10, max 50)
3. Response: same structure as annual report (summary + top_games)
4. `new_games` = games played for the first time ever (not just first time that month)
5. All filters same as STATS-001 (Published only, non-null game_id for games)

## Technical Notes

- Extend StatsReader with monthly variants or parameterize existing methods with date range
- Consider refactoring StatsReader to use DateRange VO instead of year/month params
- DoctrineStatsReader: add WHERE EXTRACT(MONTH FROM started_at) = ? to existing queries
- New handler: Stats/GetMonthlyReport (Query + Handler + Result)
- Reuse AnnualReport VO or create MonthlyReport VO
