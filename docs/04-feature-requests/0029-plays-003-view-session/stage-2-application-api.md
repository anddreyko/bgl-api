# Stage 2: Application + API Layer (PLAYS-003)

## Goal

Create GetPlay handler with visibility-based access control and wire up GET endpoint.

## Tasks

### 1. Create Query

**File:** `src/Application/Handlers/Plays/GetPlay/Query.php`

```php
final readonly class Query implements Message {
    public function __construct(
        public string $playId,
        public ?string $userId = null,  // null = anonymous
    ) {}
}
```

### 2. Create Handler

**File:** `src/Application/Handlers/Plays/GetPlay/Handler.php`

Follow `Games/GetGame/Handler.php` + visibility logic:

```php
// 1. Find play
$play = $this->plays->find($query->playId);
if ($play === null) {
    throw new NotFoundException('Session not found');
}

// 2. Check access
$this->checkAccess($play, $query->userId);

// 3. Build result with players
```

**Access control method:**

```php
private function checkAccess(Play $play, ?string $userId): void
{
    $isOwner = $userId !== null && (string)$play->getUserId() === $userId;

    // Draft: owner only
    if ($play->getStatus() === PlayStatus::Draft && !$isOwner) {
        throw new NotFoundException('Session not found');
    }

    match ($play->getVisibility()) {
        Visibility::Private => $isOwner || throw new NotFoundException('Session not found'),
        Visibility::Link, Visibility::Public => null, // anyone
        Visibility::Registered => $userId !== null || throw new AuthenticationException('Unauthorized'),
        Visibility::Friends => $isOwner || $this->isPlayerInSession($play, $userId)
            || throw new NotFoundException('Session not found'),
    };
}
```

**Friends check:** iterate play->getPlayers(), check if any player's mateId belongs to requesting userId via Mates repository. Simplified MVP: check if userId has a mate whose ID matches any player's mateId.

### 3. Create Result

**File:** `src/Application/Handlers/Plays/GetPlay/Result.php`

```php
final readonly class Result {
    public function __construct(
        public string $id,
        public ?string $name,
        public string $status,
        public string $visibility,
        public string $startedAt,
        public ?string $finishedAt,
        public ?string $gameId,
        public array $players,
    ) {}
}
```

### 4. Register in Bus

**File:** `config/common/bus.php`

Add: `[Plays\GetPlay\Query::class, Plays\GetPlay\Handler::class]`

### 5. Add Serialization

**File:** `config/_serialise-mapping.php`

### 6. Add OpenAPI Endpoint

**File:** `config/common/openapi/plays.php`

Add GET method to existing `/v1/plays/sessions/{id}` path:
- operationId: getSession
- x-message: GetPlay\Query
- x-interceptors: [OptionalAuthInterceptor]
- x-auth: [userId]
- x-map: [id => playId]
- Path param: id (required, uuid)

## Validation

```bash
composer lp:run && composer ps:run
```
