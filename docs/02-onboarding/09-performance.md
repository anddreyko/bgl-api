# Performance Rules

Actionable rules for writing performant code in BoardGameLog API. Not a theory guide -- follow these when writing code.

---

## 1. Database Queries

**Never execute a query inside a loop.** Use JOINs, batch fetching, or `WHERE IN`.

```php
// DO -- single query with JOIN FETCH
$dql = 'SELECT p, pl FROM Play p JOIN p.players pl WHERE p.userId = :userId';

// DO -- batch fetch by IDs
$this->mates->findByIds($mateIds);

// DON'T -- N+1: query per iteration
foreach ($plays as $play) {
    $players = $this->players->findByPlay($play->getId()); // query in loop
}
```

**Select only what you need.** For read-only endpoints (listings, stats), use DQL projections instead of full entity
hydration.

```php
// DO -- projection: fetches only needed columns
$dql = 'SELECT NEW GamePlayStats(g.name, COUNT(p.id)) FROM Play p JOIN p.game g GROUP BY g.id';

// DON'T -- hydrates entire entity graph when you need two fields
$plays = $this->plays->findAll(); // loads all columns + relations
return array_map(fn(Play $p) => $p->getGame()->getName(), $plays);
```

**Every column in WHERE, JOIN ON, ORDER BY must have an index.** Add indexes in Doctrine mapping, not manually in SQL.

```php
// DO -- index in mapping
'indexes' => [
    'idx_plays_session_user_id' => ['columns' => ['user_id']],
    'idx_plays_session_started_at' => ['columns' => ['started_at']],
],
```

After adding or changing queries, verify with `EXPLAIN ANALYZE` for anything >50ms.

---

## 2. Collections and Loops

**Replace `in_array()` in loops with `isset()` on a hash table.**

```php
// DO -- O(1) lookup
$existing = array_flip($existingIds);
foreach ($candidates as $candidate) {
    if (isset($existing[$candidate->getId()])) { ... }
}

// DON'T -- O(n) on every iteration = O(n*m) total
foreach ($candidates as $candidate) {
    if (in_array($candidate->getId(), $existingIds, true)) { ... }
}
```

**Avoid nested loops for matching/deduplication.** Build a lookup map first.

```php
// DO -- O(n+m)
$gamesById = [];
foreach ($games as $game) {
    $gamesById[$game->getId()->getValue()] = $game;
}
foreach ($plays as $play) {
    $game = $gamesById[$play->getGameId()->getValue()] ?? null;
}

// DON'T -- O(n*m)
foreach ($plays as $play) {
    foreach ($games as $game) {
        if ($game->getId()->equals($play->getGameId())) { ... }
    }
}
```

**Prefer iteration over recursion** when processing trees or nested structures. Recursion consumes memory per call
depth.

---

## 3. Doctrine Usage

**Use `iterate()` + `clear()` for processing large datasets.** Never load everything into memory.

```php
// DO -- constant memory
$query = $em->createQuery('SELECT p FROM Play p WHERE p.userId = :id');
foreach ($query->toIterable() as $play) {
    // process
    $em->clear(); // free memory
}

// DON'T -- loads all entities at once
$plays = $this->plays->findAll(); // 10k entities in memory
```

**Repository batch methods are mandatory** for relations. Use `findByIds()` to prevent N+1 (see section 14 of
Code Conventions).

**Lazy loading is enabled** (`enableNativeLazyObjects(true)`). Don't trigger it accidentally inside loops -- use
`JOIN FETCH` when you know you'll access a relation for every item.

---

## 4. External Service Calls

**Never call external services sequentially when results are independent.** Use concurrent requests.

```php
// DO -- parallel (Guzzle promises)
$promises = [
    'game' => $client->getAsync('/game/123'),
    'stats' => $client->getAsync('/stats/123'),
];
$results = Utils::unwrap($promises);

// DON'T -- sequential when independent
$game = $client->get('/game/123');  // waits
$stats = $client->get('/stats/123'); // waits again
```

**Cache external responses when data doesn't change often.** BGG game data is stable -- cache it.

---

## 5. SQL Patterns

**Use database features for complex aggregations** (Stats context especially).

```sql
-- DO: CTE for readable, performant aggregation
WITH play_counts AS (
    SELECT game_id, COUNT(*) as total
    FROM plays_session
    WHERE user_id = :userId AND started_at >= :from
    GROUP BY game_id
)
SELECT g.name, pc.total
FROM play_counts pc JOIN games g ON g.id = pc.game_id
ORDER BY pc.total DESC;

-- DON'T: subquery in SELECT (executes per row)
SELECT g.name,
    (SELECT COUNT(*) FROM plays_session WHERE game_id = g.id) as total
FROM games g;
```

**Denormalization is acceptable** when:

- Data comes from external services (BGG) and rarely changes.
- Aggregation across tables becomes a bottleneck with proof (metrics).
- Documented in an ADR.

---

## 6. Benchmarking

**Every performance-sensitive change must have before/after numbers.**

```bash
composer bm:base    # before changes
# ... make changes ...
composer bm:check   # assert no regression (10% tolerance)
```

**When to benchmark:**

- Changes to serialization, routing, or repository hot paths.
- New or modified Value Object / Entity constructors.
- Algorithm changes in handlers.

**16 benchmarks exist** in `tests/Benchmark/`. Add new ones for new hot paths. Results in `var/.phpbench/`.

---

## 7. Caching

**If an endpoint doesn't need real-time data, cache it.** Redis is available in Docker but not yet integrated into
application layer. Caching aspect is planned ([ADR-004](../03-decisions/004-aspects.md)).

**Rules for when caching is implemented:**

- TTL-based invalidation for MVP. Event-driven later.
- Cache at handler level (Application layer), not in repositories.
- Cache key must include user ID for user-specific data.
- Never cache mutable state (draft plays, active sessions).

---

## 8. Observability

In BGL, the **Message class** (`Command`/`Query`) is the endpoint identity. Routes map to messages via `x-message` in
OpenAPI specs (`config/common/openapi/*.php`). The `Logging` aspect already logs `message_class` on every dispatch
(`Application/Aspects/Logging.php`).

**Group metrics by message class, not by URL.**

```php
// Logging aspect already does this:
$this->logger->info('Start handle {message_class}', [
    'message_class' => $envelope->message::class, // e.g. ListPlays\Query
]);
```

When adding metrics (Prometheus counters, histograms), use the same key:

```php
// DO -- message class as label
$histogram->observe($duration, ['message' => $envelope->message::class]);

// DON'T -- URL pattern (loses type safety, duplicates routing logic)
$histogram->observe($duration, ['endpoint' => '/v1/plays']);
```

**Use specific exception classes.** Generic exceptions are invisible in metrics grouping
(see Code Conventions section 8).

```php
// DO -- filterable by type per message class
throw new GameNotFoundException(gameId: $id);

// DON'T
throw new \RuntimeException('Game not found');
```
