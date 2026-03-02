# STATS-008: Year-over-Year Comparison

## Summary

GET /v1/stats/comparison endpoint returning side-by-side statistics for multiple years
with delta indicators (growth/decline). Depends on STATS-001.

## Requirements

1. GET /v1/stats/comparison with auth required
2. Parameters: `years` (comma-separated, e.g. "2024,2025", max 5 years)
3. Response: list of year summaries + deltas between consecutive years
4. Delta fields: total_plays_delta, unique_games_delta, etc. (absolute + percentage)
5. Same summary metrics as annual report per year

## Technical Notes

- Reuses StatsReader.annualSummary() -- call once per requested year
- Delta calculation in handler (no DB logic needed)
- New handler: Stats/GetComparison (Query + Handler + Result)
- Result: list of {year, summary, delta_vs_previous}
- delta_vs_previous = null for the earliest year in the list
