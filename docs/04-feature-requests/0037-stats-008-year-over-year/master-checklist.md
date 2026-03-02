# STATS-008: Year-over-Year Comparison -- Master Checklist

## Stage 1: Domain

- [ ] VO `YearDelta` in `Domain/Stats/` -- final readonly: int $totalPlaysDelta, float $totalPlaysPercent, int $uniqueGamesDelta, float $uniqueGamesPercent, ... (mirror AnnualReport fields)
- [ ] VO `YearComparison` in `Domain/Stats/` -- final readonly: int $year, AnnualReport $summary, ?YearDelta $delta

## Stage 2: Handler

- [ ] `Stats/GetComparison/Query.php` -- string $userId, list<int> $years (validated: 1-5 items)
- [ ] `Stats/GetComparison/Handler.php`:
  - Sort years ascending
  - Call statsReader.annualSummary() for each year
  - Calculate deltas between consecutive years
  - Return Result
- [ ] `Stats/GetComparison/Result.php` -- list<YearComparison>

## Stage 3: Config

- [ ] OpenAPI: GET /v1/stats/comparison with years parameter
- [ ] bus.php: register Query -> Handler
- [ ] serialise-mapping: add Result

## Stage 4: Tests

- [ ] Unit test: YearDelta calculation logic
- [ ] Functional test: comparison with 2 years returns correct deltas
- [ ] Functional test: comparison with single year returns null delta
- [ ] Functional test: comparison isolates by user
- [ ] Web test: GET /v1/stats/comparison returns 200
- [ ] Web test: more than 5 years returns 400

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
