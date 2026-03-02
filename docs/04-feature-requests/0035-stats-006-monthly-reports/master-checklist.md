# STATS-006: Monthly Reports -- Master Checklist

## Stage 1: Domain

- [ ] Consider refactoring StatsReader to accept date range instead of year-only
- [ ] Add monthly methods to StatsReader or generalize existing ones with DateRange VO
- [ ] Reuse existing VOs (AnnualReport, GamePlayStats) or create monthly variants

## Stage 2: Infrastructure

- [ ] Extend DoctrineStatsReader with month filtering (WHERE EXTRACT(MONTH ...) = ?)
- [ ] Extend InMemoryStatsReader

## Stage 3: Handler

- [ ] `Stats/GetMonthlyReport/Query.php` -- string $userId, int $year, int $month, int $limit = 10
- [ ] `Stats/GetMonthlyReport/Handler.php`
- [ ] `Stats/GetMonthlyReport/Result.php`

## Stage 4: Config

- [ ] OpenAPI: GET /v1/stats/monthly-report with year, month, limit params
- [ ] bus.php: register Query -> Handler
- [ ] serialise-mapping: add Result
- [ ] persistence.php: StatsReader already bound (no change if reusing)

## Stage 5: Tests

- [ ] Functional test: monthly report returns data scoped to specific month
- [ ] Functional test: monthly report new_games = first play ever
- [ ] Functional test: monthly report isolates by user
- [ ] Web test: GET /v1/stats/monthly-report returns 200
- [ ] Web test: missing month parameter returns 400

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
