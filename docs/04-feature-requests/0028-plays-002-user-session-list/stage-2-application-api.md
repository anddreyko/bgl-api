# Stage 2: Application + API Layer (PLAYS-002)

## Goal

Create ListPlays handler and wire up GET /v1/plays/sessions endpoint.

## Tasks

### 1. Create Query

**File:** `src/Application/Handlers/Plays/ListPlays/Query.php`

Follow `Mates/ListMates/Query.php` pattern:

```php
final readonly class Query implements Message {
    public function __construct(
        public string $userId,
        public int $page = 1,
        public int $size = 20,
        public ?string $gameId = null,
        public ?string $from = null,
        public ?string $to = null,
    ) {}
}
```

### 2. Create Handler

**File:** `src/Application/Handlers/Plays/ListPlays/Handler.php`

Follow `Mates/ListMates/Handler.php` pattern:

- Inject: Plays, Games
- Calculate offset: ($page - 1) * $size
- Parse from/to strings to DateTimeImmutable if present
- Parse gameId to Uuid if present
- Call findAllByUser + countByUser
- Transform each Play to array:
  - id, name, status, visibility, started_at, finished_at
  - game: {id, name} if gameId present and Game found, else null
  - players: array of {id, mate_id, score, is_winner, color}
- Return Result

### 3. Create Result

**File:** `src/Application/Handlers/Plays/ListPlays/Result.php`

```php
final readonly class Result {
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {}
}
```

### 4. Register in Bus

**File:** `config/common/bus.php`

Add: `[Plays\ListPlays\Query::class, Plays\ListPlays\Handler::class]`

### 5. Add Serialization

**File:** `config/_serialise-mapping.php`

```php
Handlers\Plays\ListPlays\Result::class => static fn(Handlers\Plays\ListPlays\Result $model) => [
    'items' => $model->data,
    'total' => $model->total,
    'page' => $model->page,
    'size' => $model->size,
],
```

### 6. Add OpenAPI Endpoint

**File:** `config/common/openapi/plays.php`

Add GET method to `/v1/plays/sessions` path:
- operationId: listSessions
- x-message: ListPlays\Query
- x-interceptors: [AuthInterceptor]
- x-auth: [userId]
- Query params: page, size, game_id, from, to

## Validation

```bash
composer lp:run && composer ps:run
```
