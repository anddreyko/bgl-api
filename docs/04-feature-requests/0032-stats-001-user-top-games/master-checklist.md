# STATS-001: Annual Report (Top Games + Summary) -- Master Checklist

## Stage 1: Domain -- VOs and port [P]

- [ ] VO `GamePlayStats` in `Domain/Stats/` -- final readonly, gameId: Uuid, playCount: int, totalDurationSeconds: int, lastPlayedAt: DateTime
- [ ] VO `AnnualReport` in `Domain/Stats/` -- final readonly, year: int, totalPlays: int, uniqueGames: int, newGames: int, uniquePlayers: int, totalDurationSeconds: int, playDays: int
- [ ] Port `StatsReader` interface in `Domain/Stats/`:
  - `topGamesByYear(Uuid $userId, int $year, int $limit): list<GamePlayStats>`
  - `annualSummary(Uuid $userId, int $year): AnnualReport`

## Stage 2: Infrastructure -- DoctrineStatsReader

- [ ] `DoctrineStatsReader` in `Infrastructure/Persistence/Doctrine/Mapping/Stats/` implements StatsReader
- [ ] Constructor: `Doctrine\DBAL\Connection $connection`
- [ ] `topGamesByYear()`: GROUP BY game_id on plays_session WHERE status='published' AND game_id IS NOT NULL AND EXTRACT(YEAR FROM started_at)=?, ORDER BY play_count DESC, LIMIT
- [ ] `annualSummary()`: COUNT(*) total_plays, COUNT(DISTINCT game_id) unique_games, SUM duration, COUNT(DISTINCT DATE(started_at)) play_days from plays_session; COUNT(DISTINCT mate_id) unique_players from plays_player JOIN plays_session; new_games via NOT EXISTS subquery for prior years
- [ ] Migration: add index on plays_session (user_id, status, started_at) if not exists
- [ ] `InMemoryStatsReader` in `Infrastructure/Persistence/InMemory/` for tests

## Stage 3: Read-side handler

- [ ] `Stats/GetAnnualReport/Query.php` -- implements Message<Result>, fields: string $userId, int $year, int $limit = 10
- [ ] `Stats/GetAnnualReport/Handler.php` -- implements MessageHandler<Result, Query>:
  - Call statsReader.topGamesByYear() for top games
  - Collect gameIds, resolve names via games.findByIds()
  - Call statsReader.annualSummary() for counters
  - Return Result
- [ ] `Stats/GetAnnualReport/Result.php` -- final readonly: int $year, summary array, topGames list with game names

## Stage 4: Config + Wiring

- [ ] `config/common/persistence.php`: bind StatsReader -> DoctrineStatsReader (via Connection)
- [ ] `config/common/openapi/stats.php`: GET /v1/stats/annual-report with x-message, x-interceptors [AuthInterceptor], x-auth ['userId'], parameters [year, limit]
- [ ] `config/common/openapi/v1.php`: include stats.php
- [ ] `config/common/bus.php`: register Stats\GetAnnualReport\Query -> Handler
- [ ] `config/_serialise-mapping.php`: add Result mapping

## Stage 5: Tests

- [ ] Unit test: AnnualReport VO construction
- [ ] Unit test: GamePlayStats VO construction
- [ ] Functional test: DoctrineStatsReader.topGamesByYear returns correct order and counts
- [ ] Functional test: DoctrineStatsReader.topGamesByYear excludes Draft/Deleted sessions
- [ ] Functional test: DoctrineStatsReader.topGamesByYear excludes sessions without game_id
- [ ] Functional test: DoctrineStatsReader.annualSummary returns correct counters
- [ ] Functional test: DoctrineStatsReader.annualSummary new_games counts correctly across years
- [ ] Functional test: GetAnnualReport handler resolves game names
- [ ] Functional test: GetAnnualReport handler isolates by user
- [ ] Functional test: GetAnnualReport handler respects limit
- [ ] Web test: GET /v1/stats/annual-report?year=2025 returns 200 with auth
- [ ] Web test: GET /v1/stats/annual-report without auth returns 401
- [ ] Web test: GET /v1/stats/annual-report without year returns 400

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
