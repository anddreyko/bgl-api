# STATS-005: Categories in Annual Report -- Master Checklist

## Stage 1: Domain

- [ ] VO `CategoryStats` in `Domain/Stats/` -- final readonly: string $name, int $playCount, float $percentage
- [ ] Extend `StatsReader` interface: `categoriesByYear(Uuid $userId, int $year, int $limit): list<CategoryStats>`

## Stage 2: Infrastructure

- [ ] Extend `DoctrineStatsReader`: categoriesByYear() -- SQL with jsonb_array_elements_text(g.categories) joined with plays_session, GROUP BY category
- [ ] Extend `InMemoryStatsReader` for tests

## Stage 3: Handler extension

- [ ] Extend `GetAnnualReport/Query` with `int $categoriesLimit = 12`
- [ ] Extend `GetAnnualReport/Handler` to call statsReader.categoriesByYear()
- [ ] Extend `GetAnnualReport/Result` with categories list
- [ ] Update OpenAPI config: add categories_limit parameter and response schema
- [ ] Update serialise-mapping for extended Result

## Stage 4: Tests

- [ ] Unit test: CategoryStats VO construction
- [ ] Functional test: categoriesByYear returns correct percentages
- [ ] Functional test: multi-category games counted for each category
- [ ] Web test: GET /v1/stats/annual-report includes categories block

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
