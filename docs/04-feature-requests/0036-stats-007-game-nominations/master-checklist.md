# STATS-007: Game Nominations -- Master Checklist

## Stage 1: Domain

- [ ] Enum `NominationType` in `Domain/Stats/` -- most_played, longest_sessions, most_frequent_month
- [ ] Entity `GameNomination` in `Domain/Stats/` -- id, userId, gameId, year, type, value (int)
- [ ] Private ctor + static create(), method update(int $value)
- [ ] Repository `GameNominations` in `Domain/Stats/` extends Repository
  - findByUserAndYear(Uuid $userId, int $year): list<GameNomination>
  - findByUserGameAndYear(Uuid $userId, Uuid $gameId, int $year): list<GameNomination>
  - findByUserYearAndType(Uuid $userId, int $year, NominationType $type): ?GameNomination

## Stage 2: Infrastructure

- [ ] Doctrine mapping for GameNomination -> table stats_game_nomination
- [ ] Migration: CREATE TABLE stats_game_nomination (id, user_id, game_id, year, type, value, UNIQUE(user_id, year, type))
- [ ] DoctrineGameNominations repository
- [ ] InMemoryGameNominations for tests

## Stage 3: Nomination logic

- [ ] NominationGenerator service (Application layer) -- reads StatsReader, creates/updates GameNominations
- [ ] CLI command: stats:nominate --year --user
- [ ] Handler: Stats/GetNominations (Query + Handler + Result)
- [ ] Extend Games/GetGame handler or create separate endpoint for game nominations

## Stage 4: Config

- [ ] persistence.php: bind GameNominations
- [ ] OpenAPI: GET /v1/stats/nominations, extend GET /v1/games/{id}
- [ ] bus.php, serialise-mapping

## Stage 5: Tests

- [ ] Unit test: GameNomination entity
- [ ] Functional test: NominationGenerator creates correct nominations
- [ ] Functional test: GetNominations returns by year
- [ ] Web test: nominations endpoint

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
