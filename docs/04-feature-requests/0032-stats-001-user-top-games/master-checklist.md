# STATS-001: User's Top Games -- Master Checklist

## Stage 1: Domain

- [ ] Entity `UserGameStats` in `Domain/Stats/` -- user_id, game_id, play_count, last_played_at
- [ ] Repository interface `UserGameStatsRepository` in `Domain/Stats/` (extends Repository)
- [ ] Method `findByUser(string $userId, int $limit): list<UserGameStats>`
- [ ] Method `findByUserAndGame(string $userId, string $gameId): ?UserGameStats`
- [ ] Methods `increment(UserGameStats $stats, DateTime $playedAt)` and `decrement(UserGameStats $stats)` on entity

## Stage 2: Infrastructure

- [ ] Doctrine mapping for UserGameStats -> table `stats_user_game`
- [ ] Migration: CREATE TABLE stats_user_game (id UUID PK, user_id UUID NOT NULL, game_id UUID NOT NULL, play_count INT NOT NULL DEFAULT 0, last_played_at TIMESTAMP NOT NULL, UNIQUE(user_id, game_id))
- [ ] Index on (user_id, play_count DESC) for top-games query
- [ ] DoctrineUserGameStats repository implementation
- [ ] InMemoryUserGameStats for tests

## Stage 3: Write-side -- update stats on play lifecycle

- [ ] FinalizePlay/Handler: after play.finalize(), if gameId not null -> increment stats (find or create UserGameStats, increment play_count, update last_played_at)
- [ ] DeletePlay/Handler: after play.delete(), if gameId not null and was Published -> decrement stats

## Stage 4: Read-side -- handler

- [ ] Stats/GetUserTopGames/Query.php -- `string $userId, int $limit = 10`
- [ ] Stats/GetUserTopGames/Handler.php -- call statsRepo.findByUser(), resolve game names via games.findByIds()
- [ ] Stats/GetUserTopGames/Result.php -- `array $data` (list of {game_id, game_name, play_count, last_played_at})

## Stage 5: Config + Wiring

- [ ] config/common/persistence.php: register UserGameStatsRepository
- [ ] config/common/openapi/stats.php -- GET /v1/stats/top-games with x-message, x-interceptors [AuthInterceptor], x-auth ['userId'], parameters [limit]
- [ ] config/common/bus.php: register Stats\GetUserTopGames\Query -> Handler
- [ ] config/_serialise-mapping.php: add Result mapping

## Stage 6: Tests

- [ ] Unit test: UserGameStats.increment() / decrement()
- [ ] Functional test: FinalizePlay updates stats table
- [ ] Functional test: DeletePlay decrements stats
- [ ] Functional test: GetUserTopGames returns correct order and counts
- [ ] Functional test: GetUserTopGames empty for user without games
- [ ] Functional test: GetUserTopGames respects limit
- [ ] Functional test: GetUserTopGames isolates by user
- [ ] Web test: GET /v1/stats/top-games returns 200 with auth
- [ ] Web test: GET /v1/stats/top-games without auth returns 401

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
