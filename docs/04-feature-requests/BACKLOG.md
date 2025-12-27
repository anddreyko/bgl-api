# BoardGameLog API — Backlog

> Version: 2.0
> Date: 2025-12-27

Each task contains full context for development. Use `AGENTS.md` and `docs/` for additional information.

---

## Phase 0: Foundation

### CORE-001: Validation, Denormalization, and Serialization Components

**User Story:**
> As a developer, I want to have reusable components for working with API input/output data.

**Acceptance Criteria:**

- [ ] `Validator` contract for input data validation
- [ ] `Denormalizer` contract for converting arrays to DTOs
- [ ] `Serializer` contract for converting objects to arrays/JSON
- [ ] Contract implementations with nested object support
- [ ] Integration with Symfony Validator for validation rules
- [ ] Integration with Symfony Serializer or manual implementation
- [ ] Attribute support for field mapping

**Technical Context:**

```
Bounded Context: Core (shared)
Layers:
├── Core/Validation/Validator.php — validation contract
├── Core/Validation/ValidationResult.php — result with errors
├── Core/Serialization/Denormalizer.php — denormalization contract
├── Core/Serialization/Serializer.php — serialization contract
├── Infrastructure/Validation/SymfonyValidator.php — implementation
├── Infrastructure/Serialization/SymfonyDenormalizer.php — implementation
└── Infrastructure/Serialization/SymfonySerializer.php — implementation
```

**Dependencies:** INFRA-001

---

### CORE-002: Password Hashing Contract and Component

**User Story:**
> As a developer, I want to have an abstraction for password hashing so the algorithm can be easily changed.

**Acceptance Criteria:**

- [ ] `PasswordHasher` contract with methods `hash(string $password): string` and
  `verify(string $password, string $hash): bool`
- [ ] `BcryptPasswordHasher` implementation with configurable cost factor
- [ ] `Argon2PasswordHasher` implementation as alternative
- [ ] All implementations conform to password_hash/password_verify API
- [ ] Tests for correct hashing and verification

**Technical Context:**

```
Bounded Context: Core (shared)
Layers:
├── Core/Security/PasswordHasher.php — contract
├── Infrastructure/Security/BcryptPasswordHasher.php — bcrypt implementation
└── Infrastructure/Security/Argon2PasswordHasher.php — argon2 implementation
```

**Contract Example:**

```php
interface PasswordHasher
{
    public function hash(string $password): string;
    public function verify(string $password, string $hash): bool;
    public function needsRehash(string $hash): bool;
}
```

**Dependencies:** INFRA-001

---

### CORE-003: Mediator Pattern and Unified API Entry Point

**User Story:**
> As a developer, I want a single entry point for all API requests with automatic mapping of HTTP endpoints to
> Messages and a two-level middleware system.

**Acceptance Criteria:**

- [ ] Single `ApiAction` controller — entry point for all API requests
- [ ] `MessageBus` is called inside `ApiAction`
- [ ] Router configuration with mapping: `HTTP method + path → Message class`
- [ ] **Interceptors** (Presentation layer) — HTTP request processing
- [ ] **Aspects** (Application layer) — cross-cutting concerns for use cases
- [ ] Interceptors execute BEFORE passing to MessageBus
- [ ] Aspects execute INSIDE MessageBus pipeline

**Two-Layer Middleware Architecture:**

```
HTTP Request
    │
    ▼
┌────────────────────────────────────────────────────────────┐
│                  PRESENTATION LAYER                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Interceptors (HTTP)                    │   │
│  │  ┌─────────────┐ ┌─────────────┐ ┌───────────────┐  │   │
│  │  │ Auth        │→│ Denormalize │→│ Rate Limit    │  │   │
│  │  │ Interceptor │ │ Interceptor │ │ Interceptor   │  │   │
│  │  └─────────────┘ └─────────────┘ └───────────────┘  │   │
│  └─────────────────────────────────────────────────────┘   │
│                          │                                 │
│                          ▼                                 │
│                    ┌───────────┐                           │
│                    │ ApiAction │ ← Single entry point      │
│                    └───────────┘                           │
└────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌────────────────────────────────────────────────────────────┐
│                  APPLICATION LAYER                         │
│  ┌─────────────────────────────────────────────────────┐   │
│  │               MessageBus Pipeline                   │   │
│  │  ┌─────────────┐ ┌─────────────┐ ┌───────────────┐  │   │
│  │  │ Logging     │→│Transactional│→│ Metrics       │  │   │
│  │  │ Aspect      │ │ Aspect      │ │ Aspect        │  │   │
│  │  └─────────────┘ └─────────────┘ └───────────────┘  │   │
│  │                         │                           │   │
│  │                         ▼                           │   │
│  │                   ┌──────────┐                      │   │
│  │                   │ Handler  │                      │   │
│  │                   └──────────┘                      │   │
│  └─────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────┘
```

**Technical Context:**

```
Layers:
├── Core/Messages/Message.php — base interface
├── Core/Messages/Command.php — command (state change)
├── Core/Messages/Query.php — query (read)
├── Core/Messages/MessageBus.php — message bus contract
├── Core/Messages/MessageMiddleware.php — contract for Aspects
│
├── Application/Aspects/LoggingAspect.php — use case logging
├── Application/Aspects/TransactionalAspect.php — transaction management
├── Application/Aspects/MetricsAspect.php — metrics collection
├── Application/Aspects/CachingAspect.php — Query caching
│
├── Infrastructure/MessageBus/TacticianMessageBus.php — implementation
├── Infrastructure/MessageBus/MiddlewareResolver.php — Aspects resolver
│
├── Presentation/Api/ApiAction.php — single controller
├── Presentation/Api/RouteMessageMap.php — mapping configuration
├── Presentation/Api/Interceptors/Interceptor.php — contract
├── Presentation/Api/Interceptors/AuthInterceptor.php — authentication
├── Presentation/Api/Interceptors/AuthorizationInterceptor.php — authorization
├── Presentation/Api/Interceptors/DenormalizationInterceptor.php — HTTP → DTO
├── Presentation/Api/Interceptors/SerializationInterceptor.php — Result → JSON
├── Presentation/Api/Interceptors/ValidationInterceptor.php — input validation
├── Presentation/Api/Interceptors/RateLimitInterceptor.php — request limiting
│
└── config/
    ├── routes.php — HTTP → Message mapping
    ├── interceptors.php — Interceptors configuration
    └── aspects.php — Aspects pipeline configuration
```

**Interceptor Contract (Presentation):**

```php
interface Interceptor
{
    /**
     * Processes HTTP request before/after passing to MessageBus.
     * Can modify Request, abort processing, enrich Response.
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface;
}
```

**Aspect Contract (Application):**

```php
interface MessageMiddleware
{
    /**
     * Processes Message in MessageBus pipeline.
     * Cross-cutting concerns: logging, transactions, metrics, caching.
     */
    public function handle(Message $message, callable $next): mixed;
}
```

**RouteMessageMap Configuration:**

```php
// config/routes.php
return [
    'POST /api/auth/register' => [
        'message' => \App\Application\Handlers\Auth\Register\Command::class,
        'interceptors' => ['denormalization', 'validation'],
        'public' => true, // Does not require authentication
    ],
    'POST /api/auth/login' => [
        'message' => \App\Application\Handlers\Auth\Login\Command::class,
        'interceptors' => ['denormalization', 'validation'],
        'public' => true,
    ],
    'GET /api/plays' => [
        'message' => \App\Application\Handlers\Plays\ListPlays\Query::class,
        'interceptors' => ['auth', 'denormalization'],
        'public' => false,
    ],
    'POST /api/plays' => [
        'message' => \App\Application\Handlers\Plays\CreatePlay\Command::class,
        'interceptors' => ['auth', 'denormalization', 'validation'],
        'public' => false,
    ],
    // ...
];
```

**ApiAction (Single Entry Point):**

```php
final readonly class ApiAction implements RequestHandlerInterface
{
    public function __construct(
        private RouteMessageMap $routeMap,
        private MessageBus $messageBus,
        private Denormalizer $denormalizer,
        private Serializer $serializer,
        private InterceptorPipeline $interceptors,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 1. Get route configuration
        $route = $this->routeMap->match($request);

        // 2. Execute Interceptors pipeline
        $request = $this->interceptors->process($request, $route);

        // 3. Denormalize HTTP data to Message
        $message = $this->denormalizer->denormalize(
            $request->getParsedBody(),
            $route->messageClass,
        );

        // 4. Send to MessageBus (Aspects execute inside)
        $result = $this->messageBus->handle($message);

        // 5. Serialize result to JSON Response
        return $this->serializer->toResponse($result);
    }
}
```

**Interceptors Pipeline (Presentation):**

| Interceptor                  | Responsibility              | Order |
|------------------------------|-----------------------------|-------|
| `RateLimitInterceptor`       | Request rate limiting       | 1     |
| `AuthInterceptor`            | JWT extraction & validation | 2     |
| `AuthorizationInterceptor`   | Access rights checking      | 3     |
| `DenormalizationInterceptor` | HTTP body → DTO/Message     | 4     |
| `ValidationInterceptor`      | Input data validation       | 5     |

**Aspects Pipeline (Application):**

| Aspect                | Responsibility            | Order |
|-----------------------|---------------------------|-------|
| `LoggingAspect`       | Entry/exit/error logging  | 1     |
| `MetricsAspect`       | Execution metrics         | 2     |
| `TransactionalAspect` | DB transaction management | 3     |
| `CachingAspect`       | Query result caching      | 4     |

**Aspects Configuration:**

```php
// config/aspects.php
return [
    // Order matters — executed as nested (first in, last out)
    LoggingAspect::class,
    MetricsAspect::class,
    TransactionalAspect::class,
    CachingAspect::class,
];
```

**Dependencies:** CORE-001, CORE-002, INFRA-001

---

### CORE-004: API Response Contracts

**User Story:**
> As a developer, I want to have standardized API response format for success and error responses across all endpoints.

**Acceptance Criteria:**

- [ ] `SuccessResponse` contract with data payload and optional metadata
- [ ] `ErrorResponse` contract with error code, message, and optional details
- [ ] Common response interface for polymorphic handling
- [ ] Support for HTTP status code binding
- [ ] Consistent JSON structure across all API endpoints
- [ ] Integration with `Serializer` from CORE-001
- [ ] Tests pass (`make scan`)

**Technical Context:**

```
Bounded Context: Core (shared)
Layers:
├── src/Presentation/Api/V1/Responses/ResponseInterface.php — common contract
├── src/Presentation/Api/V1/Responses/SuccessResponse.php — success response
├── src/Presentation/Api/V1/Responses/ErrorResponse.php — error response
└── src/Presentation/Api/V1/Responses/ValidationErrorResponse.php — validation errors
```

**Response Structure Examples:**

```php
// Success response
final readonly class SuccessResponse implements ResponseInterface
{
    public function __construct(
        public mixed $data = null,
        public ?array $meta = null,
        public int $statusCode = 200,
    ) {}
}

// Error response
final readonly class ErrorResponse implements ResponseInterface
{
    public function __construct(
        public string $error,
        public string $message,
        public ?array $details = null,
        public int $statusCode = 400,
    ) {}
}
```

**JSON Output Examples:**

```json
// Success
{
    "success": true,
    "data": {
        ...
    },
    "meta": {
        "page": 1,
        "total": 100
    }
}

// Error
{
    "success": false,
    "error": "validation_error",
    "message": "Invalid input data",
    "details": {
        "email": [
            "Email is required"
        ]
    }
}
```

**Dependencies:** CORE-001

---

## Phase 1: MVP

### AUTH-001: User Registration with Email Confirmation

**User Story:**
> As a new user, I want to register with email and confirm it via magic link, and also have the option to use passkey
> for subsequent authorization.

**Acceptance Criteria:**

- [ ] POST `/api/auth/register` accepts `{email, password, username?}`
- [ ] Email is unique, validated by format
- [ ] Password minimum 8 characters, hashed via `PasswordHasher` contract
- [ ] After registration, email is sent with magic link for confirmation
- [ ] GET `/api/auth/confirm/:token` confirms email and activates account
- [ ] Magic link valid for 24 hours, single-use
- [ ] User cannot log in until email is confirmed
- [ ] POST `/api/auth/register/passkey` — passkey registration/linking (WebAuthn)
- [ ] On duplicate email — 409 Conflict
- [ ] On invalid data — 422 with error details
- [ ] Uses `league/oauth2-server` for OAuth flow

**Technical Context:**

```
Bounded Context: Auth
Layers:
├── Domain/Auth/Entities/User.php — entity with Email, PasswordHash VO
├── Domain/Auth/Entities/EmailConfirmationToken.php — confirmation token
├── Domain/Auth/Entities/Passkey.php — WebAuthn credential
├── Domain/Auth/ValueObjects/Email.php — email validation
├── Domain/Auth/ValueObjects/PasswordHash.php — hash wrapper
├── Domain/Auth/ValueObjects/UserStatus.php — pending, active, blocked
├── Domain/Auth/Repositories/Users.php — repository interface
├── Domain/Auth/Repositories/EmailConfirmationTokens.php — interface
├── Domain/Auth/Repositories/Passkeys.php — interface
├── Domain/Auth/Services/PasswordHasher.php — contract (in Core)
├── Application/Handlers/Auth/Register/Command.php
├── Application/Handlers/Auth/Register/Handler.php
├── Application/Handlers/Auth/ConfirmEmail/Command.php
├── Application/Handlers/Auth/ConfirmEmail/Handler.php
├── Application/Handlers/Auth/RegisterPasskey/Command.php
├── Application/Handlers/Auth/RegisterPasskey/Handler.php
├── Infrastructure/Persistence/Doctrine/Users.php — implementation
├── Infrastructure/Auth/LeagueOAuth2Server.php — league/oauth2-server integration
└── Infrastructure/Mail/EmailSender.php — email sending
```

**Routes (config/routes.php):**

```php
'POST /api/auth/register' => [
    'message' => Auth\Register\Command::class,
    'interceptors' => ['denormalization', 'validation'],
    'public' => true,
],
'GET /api/auth/confirm/{token}' => [
    'message' => Auth\ConfirmEmail\Command::class,
    'interceptors' => ['denormalization'],
    'public' => true,
],
'POST /api/auth/register/passkey' => [
    'message' => Auth\RegisterPasskey\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
```

**Command:**

```php
final readonly class Command implements \Core\Messages\Command
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $username = null,
    ) {}
}
```

**DB Migration:**

```sql
CREATE TABLE users
(
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email         VARCHAR(255) UNIQUE NOT NULL,
    username      VARCHAR(255),
    password_hash VARCHAR(255)        NOT NULL,
    status        VARCHAR(50)      DEFAULT 'pending', -- pending, active, blocked
    created_at    TIMESTAMP        DEFAULT NOW(),
    confirmed_at  TIMESTAMP
);
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_status ON users (status);

CREATE TABLE email_confirmation_tokens
(
    id         UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id    UUID REFERENCES users (id) ON DELETE CASCADE,
    token      VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP           NOT NULL,
    used_at    TIMESTAMP,
    created_at TIMESTAMP        DEFAULT NOW()
);
CREATE INDEX idx_email_tokens_token ON email_confirmation_tokens (token);

CREATE TABLE passkeys
(
    id            UUID PRIMARY KEY      DEFAULT gen_random_uuid(),
    user_id       UUID REFERENCES users (id) ON DELETE CASCADE,
    credential_id BYTEA UNIQUE NOT NULL,
    public_key    BYTEA        NOT NULL,
    sign_count    INTEGER      NOT NULL DEFAULT 0,
    name          VARCHAR(255),
    created_at    TIMESTAMP             DEFAULT NOW()
);
CREATE INDEX idx_passkeys_user ON passkeys (user_id);
CREATE INDEX idx_passkeys_credential ON passkeys (credential_id);
```

**Environment Variables:**

```env
MAIL_FROM=noreply@boardgamelog.app
MAIL_SMTP_HOST=smtp.example.com
APP_URL=https://boardgamelog.app
```

**Dependencies:** CORE-001, CORE-002, INFRA-001

---

### AUTH-002: Authentication (Login)

**User Story:**
> As a registered user, I want to log in with email and password or passkey to get an access token.

**Acceptance Criteria:**

- [ ] POST `/api/auth/login` accepts `{email, password}`
- [ ] POST `/api/auth/login/passkey` — login via passkey (WebAuthn)
- [ ] Verifies password via `PasswordHasher::verify()`
- [ ] Returns `{accessToken, refreshToken, expiresIn}`
- [ ] JWT contains ONLY: sub (userId), iat, exp — **no email**
- [ ] Access token and refresh token are **NOT stored in DB** — generated on request
- [ ] Tokens validated by signature on each request
- [ ] User must have 'active' status (email confirmed)
- [ ] On wrong credentials — 401 Unauthorized
- [ ] On unconfirmed email — 403 Forbidden with reason
- [ ] Uses `league/oauth2-server` for token generation

**Technical Context:**

```
Bounded Context: Auth
Layers:
├── Domain/Auth/Services/TokenGenerator.php — JWT generation contract
├── Domain/Auth/Services/TokenValidator.php — JWT validation contract
├── Application/Handlers/Auth/Login/Command.php
├── Application/Handlers/Auth/Login/Handler.php
├── Application/Handlers/Auth/LoginWithPasskey/Command.php
├── Application/Handlers/Auth/LoginWithPasskey/Handler.php
├── Infrastructure/Auth/JwtTokenGenerator.php — JWT implementation
├── Infrastructure/Auth/JwtTokenValidator.php — validation implementation
└── Infrastructure/Auth/LeagueOAuth2Server.php — integration
```

**Routes (config/routes.php):**

```php
'POST /api/auth/login' => [
    'message' => Auth\Login\Command::class,
    'interceptors' => ['denormalization', 'validation'],
    'public' => true,
],
'POST /api/auth/login/passkey' => [
    'message' => Auth\LoginWithPasskey\Command::class,
    'interceptors' => ['denormalization', 'validation'],
    'public' => true,
],
```

**JWT Payload (minimal):**

```json
{
    "sub": "user-uuid",
    "iat": 1234567890,
    "exp": 1234571490
}
```

**Refresh Token Payload:**

```json
{
    "sub": "user-uuid",
    "type": "refresh",
    "iat": 1234567890,
    "exp": 1235176690
}
```

**Environment Variables:**

```env
JWT_SECRET=your-secret-key-min-32-chars
JWT_TTL=3600
JWT_REFRESH_TTL=604800
JWT_ALGORITHM=HS256
```

**Dependencies:** AUTH-001

---

### AUTH-003: Token Refresh

**User Story:**
> As a user with an expired token, I want to refresh it without re-entering my password.

**Acceptance Criteria:**

- [ ] POST `/api/auth/refresh` accepts `{refreshToken}`
- [ ] Validates refresh token by signature and expiration
- [ ] Verifies user exists and is active
- [ ] Generates new accessToken + refreshToken pair
- [ ] On invalid token — 401 Unauthorized
- [ ] Tokens are NOT stored in DB — validation by signature only

**Technical Context:**

```
Bounded Context: Auth
Layers:
├── Application/Handlers/Auth/RefreshToken/Command.php
└── Application/Handlers/Auth/RefreshToken/Handler.php
```

**Routes (config/routes.php):**

```php
'POST /api/auth/refresh' => [
    'message' => Auth\RefreshToken\Command::class,
    'interceptors' => ['denormalization', 'validation'],
    'public' => true,
],
```

**Logic:**

1. Validate refresh token by JWT signature
2. Check expiration (exp)
3. Extract userId from payload
4. Verify user existence and status
5. Generate new token pair
6. Return new tokens

**Dependencies:** AUTH-002

---

### AUTH-004: Authentication and Authorization Interceptors

**User Story:**
> As a system, I want to verify JWT token and access rights on protected endpoints.

**Acceptance Criteria:**

- [ ] `AuthInterceptor` (Presentation layer) — JWT extraction and validation from HTTP header
- [ ] `AuthorizationInterceptor` (Presentation layer) — endpoint access rights checking
- [ ] Interceptors integrated into pipeline from CORE-003
- [ ] Token extraction from `Authorization: Bearer <token>`
- [ ] JWT signature validation via `TokenValidator`
- [ ] Token expiration check
- [ ] userId added to request attributes for passing to Message
- [ ] Role-based access control (RBAC) support for authorization
- [ ] On missing token — 401 Unauthorized
- [ ] On invalid token — 401 Unauthorized
- [ ] On insufficient rights — 403 Forbidden
- [ ] Routes with `public: true` (from RouteMessageMap) are skipped

**Technical Context:**

```
Layers (per CORE-003):
├── Presentation/Api/Interceptors/AuthInterceptor.php — authentication (HTTP)
├── Presentation/Api/Interceptors/AuthorizationInterceptor.php — authorization (HTTP)
├── Infrastructure/Auth/JwtTokenValidator.php — token validation
└── config/routes.php — public/private routes configuration
```

**AuthInterceptor (Presentation layer):**

```php
final readonly class AuthInterceptor implements Interceptor
{
    public function __construct(
        private TokenValidator $tokenValidator,
        private RouteMessageMap $routeMap,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $route = $this->routeMap->match($request);

        // Skip public routes
        if ($route->isPublic()) {
            return $handler->handle($request);
        }

        $token = $this->extractBearerToken($request);
        if ($token === null) {
            throw new UnauthorizedException('Token required');
        }

        $payload = $this->tokenValidator->validate($token);

        // Add userId to request for passing to Message
        return $handler->handle(
            $request->withAttribute('userId', $payload['sub'])
        );
    }

    private function extractBearerToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}
```

**AuthorizationInterceptor (Presentation layer):**

```php
final readonly class AuthorizationInterceptor implements Interceptor
{
    public function __construct(
        private RouteMessageMap $routeMap,
        private AccessChecker $accessChecker,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $route = $this->routeMap->match($request);
        $userId = $request->getAttribute('userId');

        if (!$this->accessChecker->canAccess($userId, $route->permissions())) {
            throw new ForbiddenException('Access denied');
        }

        return $handler->handle($request);
    }
}
```

**Configuration in routes.php:**

```php
return [
    'POST /api/auth/register' => [
        'message' => RegisterCommand::class,
        'public' => true, // AuthInterceptor skips
    ],
    'GET /api/plays' => [
        'message' => ListPlaysQuery::class,
        'public' => false, // Requires authentication
        'permissions' => ['plays:read'], // For AuthorizationInterceptor
    ],
];
```

**Dependencies:** AUTH-002, CORE-003

---

### GAMES-001: Game Search via BGG

**User Story:**
> As a user, I want to search for board games by name to add them to my plays.

**Acceptance Criteria:**

- [ ] GET `/api/games/search?q=carcassonne` returns game list
- [ ] GET `/api/games/search?q=carcassonne&fields=id,name,bggId` — request only specific fields
- [ ] Minimum 3 characters for search
- [ ] Results via composer package for BGG API
- [ ] Uses `Searchable` contract for search
- [ ] **MVP:** Data stored in PostgreSQL, caching in Redis
- [ ] **Expansion:** Caching in Redis with 24h TTL
- [ ] **Scaling:** Search migration to Elasticsearch
- [ ] On BGG unavailability — return from local DB or 503
- [ ] Game statistics may not be requested (fields parameter)

**Technical Context:**

```
Bounded Context: Games + Sync
Layers:
├── Core/Listing/Searchable.php — search contract (exists)
├── Domain/Sync/Services/GameCatalogProvider.php — Port (interface)
├── Domain/Games/Entities/Game.php
├── Domain/Games/ValueObjects/BggId.php
├── Domain/Games/Repositories/Games.php — extends Searchable
├── Domain/Games/Filters/GameFilter.php — search filters
├── Application/Handlers/Games/SearchGames/Query.php
├── Application/Handlers/Games/SearchGames/Handler.php
├── Infrastructure/Sync/Bgg/BggCatalogProvider.php — Adapter (composer package)
└── Infrastructure/Persistence/Doctrine/Games.php — implementation with Searchable
```

**Routes (config/routes.php):**

```php
'GET /api/games/search' => [
    'message' => Games\SearchGames\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**BGG Composer Package:**

```json
{
    "require": {
        "bgg/api-client": "^1.0"
    }
}
```

**Query with Fields Support:**

```php
final readonly class Query implements \Core\Messages\Query
{
    public function __construct(
        public string $query,
        /** @var list<string>|null */
        public ?array $fields = null, // null = all fields
        public int $limit = 20,
    ) {}
}
```

**Response DTO:**

```php
final readonly class GameSearchResult
{
    public function __construct(
        public string $id,
        public int $bggId,
        public string $name,
        public ?int $yearPublished = null,
        public ?string $imageUrl = null,
        // Stats may be absent if not requested
        public ?GameStats $stats = null,
    ) {}
}
```

**DB Migration:**

```sql
CREATE TABLE games
(
    id               UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    bgg_id           INTEGER UNIQUE,
    name             VARCHAR(255) NOT NULL,
    year_published   INTEGER,
    min_players      INTEGER,
    max_players      INTEGER,
    playing_time_min INTEGER,
    playing_time_max INTEGER,
    image_url        TEXT,
    type             VARCHAR(50)      DEFAULT 'base',
    created_at       TIMESTAMP        DEFAULT NOW(),
    updated_at       TIMESTAMP
);
CREATE INDEX idx_games_bgg_id ON games (bgg_id);
CREATE INDEX idx_games_name ON games (name);
CREATE INDEX idx_games_name_gin ON games USING gin (to_tsvector('english', name));
```

**Environment Variables:**

```env
BGG_API_URL=https://boardgamegeek.com/xmlapi2
REDIS_URL=redis://localhost:6379
SEARCH_BACKEND=postgres # postgres | redis | elasticsearch
```

**Dependencies:** INFRA-001

---

### GAMES-002: Game Details

**User Story:**
> As a user, I want to see detailed information about a game.

**Acceptance Criteria:**

- [ ] GET `/api/games/:id` returns game details
- [ ] GET `/api/games/:id?fields=id,name,stats` — request only specific fields
- [ ] If game not in DB, fetches from BGG and saves
- [ ] Returns: all Game fields + user play statistics (if requested)
- [ ] On non-existent ID — 404

**Technical Context:**

```
Bounded Context: Games
Layers:
├── Application/Handlers/Games/GetGame/Query.php
└── Application/Handlers/Games/GetGame/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/games/{id}' => [
    'message' => Games\GetGame\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "id": "uuid",
    "bggId": 822,
    "name": "Carcassonne",
    "yearPublished": 2000,
    "minPlayers": 2,
    "maxPlayers": 5,
    "playingTimeMin": 30,
    "playingTimeMax": 45,
    "imageUrl": "https://...",
    "userStats": {
        "totalPlays": 15,
        "lastPlayedAt": "2025-01-10"
    }
}
```

**Dependencies:** GAMES-001

---

### PLAYS-001: Create Game Session

**User Story:**
> As a user, I want to record a board game session with participants and results.

**Acceptance Criteria:**

- [ ] POST `/api/plays` creates a new session
- [ ] Field `gameId` is **optional** (session can exist without game link)
- [ ] Fields `startedAt` and `finishedAt` — both optional
- [ ] Validation: `startedAt` cannot be greater than `finishedAt`
- [ ] Field `players[]` is required, minimum 1 participant
- [ ] Each player: `mateId` (reference to co-players directory), `score?`, `isWinner?`, `color?`
- [ ] `startedAt` and `finishedAt` cannot be in the future
- [ ] `PlayCreated` event generated
- [ ] Returns created session with ID

**Technical Context:**

```
Bounded Context: Plays
Layers:
├── Domain/Plays/Entities/Play.php — Aggregate Root
├── Domain/Plays/Entities/Player.php — Entity inside aggregate
├── Domain/Plays/ValueObjects/PlayId.php
├── Domain/Plays/ValueObjects/Score.php
├── Domain/Plays/ValueObjects/Visibility.php — visibility level
├── Domain/Plays/Events/PlayCreated.php — Domain Event
├── Domain/Plays/Repositories/Plays.php
├── Application/Handlers/Plays/CreatePlay/Command.php
├── Application/Handlers/Plays/CreatePlay/Handler.php
└── Infrastructure/Persistence/Doctrine/Plays.php
```

**Routes (config/routes.php):**

```php
'POST /api/plays' => [
    'message' => Plays\CreatePlay\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
```

**Command:**

```php
final readonly class Command implements \Core\Messages\Command
{
    public function __construct(
        public string $userId,
        public ?string $gameId = null, // Optional
        public ?DateTimeImmutable $startedAt = null, // Optional
        public ?DateTimeImmutable $finishedAt = null, // Optional
        public array $players, // PlayerDto[]
        public ?string $location = null,
        public ?string $notes = null,
        public Visibility $visibility = Visibility::Private,
    ) {}
}
```

**PlayerDto:**

```php
final readonly class PlayerDto
{
    public function __construct(
        public string $mateId, // Reference to co-players directory
        public ?int $score = null,
        public bool $isWinner = false,
        public ?string $color = null,
    ) {}
}
```

**Visibility Enum:**

```php
enum Visibility: string
{
    case Private = 'private';       // Only for self
    case Link = 'link';             // By link (anyone with link, including unauthenticated)
    case Friends = 'friends';       // For friends (confirmed mates)
    case Registered = 'registered'; // For all registered users
    case Public = 'public';         // For entire internet (indexed, visible in profile)
}
```

**DB Migration:**

```sql
CREATE TABLE plays
(
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id     UUID REFERENCES users (id) ON DELETE CASCADE,
    game_id     UUID REFERENCES games (id), -- Can be NULL
    started_at  TIMESTAMP,                  -- Optional
    finished_at TIMESTAMP,                  -- Optional
    location    VARCHAR(255),
    notes       TEXT,
    visibility  VARCHAR(50)      DEFAULT 'private',
    sync_status VARCHAR(50)      DEFAULT 'not_synced',
    created_at  TIMESTAMP        DEFAULT NOW(),
    CONSTRAINT chk_dates CHECK (started_at IS NULL OR finished_at IS NULL OR started_at <= finished_at)
);
CREATE INDEX idx_plays_user ON plays (user_id);
CREATE INDEX idx_plays_game ON plays (game_id);
CREATE INDEX idx_plays_started ON plays (started_at DESC);
CREATE INDEX idx_plays_visibility ON plays (visibility);

CREATE TABLE players
(
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    play_id         UUID REFERENCES plays (id) ON DELETE CASCADE,
    mate_id         UUID REFERENCES mates (id), -- Reference to directory
    score           INTEGER,
    is_winner       BOOLEAN          DEFAULT FALSE,
    is_first_player BOOLEAN          DEFAULT FALSE,
    color           VARCHAR(50)
);
CREATE INDEX idx_players_play ON players (play_id);
CREATE INDEX idx_players_mate ON players (mate_id);
```

**Domain Event:**

```php
final readonly class PlayCreated implements \Core\Messages\Event
{
    public function __construct(
        public string $playId,
        public string $userId,
        public ?string $gameId,
        public ?DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $finishedAt,
    ) {}
}
```

**Dependencies:** AUTH-004, MATES-001, GAMES-001 (optional)

---

### PLAYS-002: User Session List

**User Story:**
> As a user, I want to see my play history with filtering and pagination.

**Acceptance Criteria:**

- [ ] GET `/api/plays` returns current user's session list
- [ ] Pagination: `?page=1&limit=20` (default limit=20, max=100)
- [ ] Filters: `?gameId=uuid`, `?from=2025-01-01`, `?to=2025-01-31`
- [ ] Sorted by date (newest first)
- [ ] Returns: `{items: Play[], total, page, limit}`
- [ ] Each Play includes game (name, imageUrl) and players

**Technical Context:**

```
Bounded Context: Plays
Layers:
├── Core/Listing/Filter.php — base filter class
├── Core/Listing/Pagination.php — pagination VO
├── Domain/Plays/Filters/PlayFilter.php
├── Application/Handlers/Plays/ListPlays/Query.php
└── Application/Handlers/Plays/ListPlays/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/plays' => [
    'message' => Plays\ListPlays\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Query:**

```php
final readonly class Query implements \Core\Messages\Query
{
    public function __construct(
        public string $userId,
        public int $page = 1,
        public int $limit = 20,
        public ?string $gameId = null,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
    ) {}
}
```

**Response:**

```json
{
    "items": [
        {
            "id": "uuid",
            "startedAt": "2025-01-15T19:30:00Z",
            "finishedAt": "2025-01-15T21:00:00Z",
            "location": "Home",
            "visibility": "private",
            "game": {
                "id": "uuid",
                "name": "Carcassonne",
                "imageUrl": "https://..."
            },
            "players": [
                {
                    "mate": {
                        "id": "uuid",
                        "name": "Alice"
                    },
                    "score": 85,
                    "isWinner": true
                },
                {
                    "mate": {
                        "id": "uuid",
                        "name": "Bob"
                    },
                    "score": 72,
                    "isWinner": false
                }
            ]
        }
    ],
    "total": 150,
    "page": 1,
    "limit": 20
}
```

**Dependencies:** PLAYS-001

---

### PLAYS-003: View Session

**User Story:**
> As a user, I want to see details of a specific play.

**Acceptance Criteria:**

- [ ] GET `/api/plays/:id` returns full session data
- [ ] Includes: game details, all players, notes, location
- [ ] Visibility check:
    - `private` — owner only
    - `link` — anyone with link (including unauthenticated users)
    - `friends` — owner or confirmed mates
    - `registered` — any registered user
    - `public` — anyone (including unauthenticated users), indexed by search engines
- [ ] Difference between `link` and `public`: plays with `link` are not displayed in public lists and profile,
  accessible only via direct link
- [ ] On visibility mismatch — **403 Forbidden** (not 404!)
- [ ] On non-existent ID — 404 Not Found

**Technical Context:**

```
Bounded Context: Plays
Layers:
├── Domain/Plays/Services/VisibilityChecker.php — access checking
├── Application/Handlers/Plays/GetPlay/Query.php
└── Application/Handlers/Plays/GetPlay/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/plays/{id}' => [
    'message' => Plays\GetPlay\Query::class,
    'interceptors' => ['denormalization'], // auth optional — check in VisibilityChecker
    'public' => true, // Access controlled via Visibility
],
```

**VisibilityChecker:**

```php
final readonly class VisibilityChecker
{
    public function canView(Play $play, ?string $viewerId, Mates $mates): bool
    {
        return match ($play->visibility()) {
            Visibility::Private => $play->userId() === $viewerId,
            Visibility::Link => true, // Anyone with link, including anonymous
            Visibility::Friends => $play->userId() === $viewerId
                || ($viewerId && $mates->areFriends($play->userId(), $viewerId)),
            Visibility::Registered => $viewerId !== null,
            Visibility::Public => true,
        };
    }

    /**
     * Checks if play should be displayed in public lists.
     * Link plays are not displayed in profile and public feeds.
     */
    public function isPubliclyListed(Play $play): bool
    {
        return $play->visibility() === Visibility::Public;
    }
}
```

**Dependencies:** PLAYS-001

---

### PLAYS-004: Update Session

**User Story:**
> As a user, I want to correct play data if I made a mistake.

**Acceptance Criteria:**

- [ ] PUT `/api/plays/:id` updates session
- [ ] Can change: `gameId`, `startedAt`, `finishedAt`, `location`, `notes`, `players`, `visibility`
- [ ] **`gameId` can be changed or removed** (set to null)
- [ ] Only owner can edit
- [ ] On players change — full list replacement
- [ ] Validation: `startedAt` <= `finishedAt`
- [ ] `PlayUpdated` event generated

**Technical Context:**

```
Bounded Context: Plays
Layers:
├── Domain/Plays/Events/PlayUpdated.php
├── Application/Handlers/Plays/UpdatePlay/Command.php
└── Application/Handlers/Plays/UpdatePlay/Handler.php
```

**Routes (config/routes.php):**

```php
'PUT /api/plays/{id}' => [
    'message' => Plays\UpdatePlay\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
```

**Command:**

```php
final readonly class Command implements \Core\Messages\Command
{
    public function __construct(
        public string $playId,
        public string $userId,
        public ?string $gameId = null, // null = remove link
        public bool $removeGame = false, // Explicit gameId removal
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $finishedAt = null,
        public ?string $location = null,
        public ?string $notes = null,
        public ?array $players = null, // null = don't change
        public ?Visibility $visibility = null,
    ) {}
}
```

**Dependencies:** PLAYS-003

---

### PLAYS-005: Delete Session

**User Story:**
> As a user, I want to delete an erroneously created play.

**Acceptance Criteria:**

- [ ] DELETE `/api/plays/:id` deletes session
- [ ] Cascade deletion of players
- [ ] Only owner can delete
- [ ] Returns 204 No Content
- [ ] On non-existent ID — 404
- [ ] `PlayDeleted` event generated

**Technical Context:**

```
Bounded Context: Plays
Layers:
├── Domain/Plays/Events/PlayDeleted.php
├── Application/Handlers/Plays/DeletePlay/Command.php
└── Application/Handlers/Plays/DeletePlay/Handler.php
```

**Routes (config/routes.php):**

```php
'DELETE /api/plays/{id}' => [
    'message' => Plays\DeletePlay\Command::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Dependencies:** PLAYS-003

---

### MATES-001: Co-Players Directory Management

**User Story:**
> As a user, I want to maintain my own list of co-players who may not be registered on the site.

**Acceptance Criteria:**

- [ ] POST `/api/mates` creates a record in co-players directory
- [ ] Accepts: `{name, notes?}`
- [ ] Co-player is **not linked to userId** — this is user's own directory
- [ ] Each user has their own independent co-players list
- [ ] GET `/api/mates` — list all user's co-players
- [ ] GET `/api/mates/:id` — co-player details
- [ ] PUT `/api/mates/:id` — update name/notes
- [ ] DELETE `/api/mates/:id` — delete (soft delete or usage check)
- [ ] **Expansion:** Ability to link co-player to registered user

**Technical Context:**

```
Bounded Context: Mates (new context)
Layers:
├── Domain/Mates/Entities/Mate.php — directory record
├── Domain/Mates/ValueObjects/MateId.php
├── Domain/Mates/Repositories/Mates.php — interface
├── Application/Handlers/Mates/CreateMate/Command.php
├── Application/Handlers/Mates/CreateMate/Handler.php
├── Application/Handlers/Mates/ListMates/Query.php
├── Application/Handlers/Mates/ListMates/Handler.php
├── Application/Handlers/Mates/GetMate/Query.php
├── Application/Handlers/Mates/UpdateMate/Command.php
├── Application/Handlers/Mates/DeleteMate/Command.php
├── Infrastructure/Persistence/Doctrine/Mates.php — implementation
└── Presentation/Api/Actions/Mates/...
```

**Entity:**

```php
final class Mate
{
    private function __construct(
        private MateId $id,
        private UserId $ownerId,      // Directory owner
        private string $name,
        private ?string $notes,
        private ?UserId $linkedUserId, // Expansion: link to user
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        UserId $ownerId,
        string $name,
        ?string $notes = null,
    ): self {
        return new self(
            MateId::generate(),
            $ownerId,
            $name,
            $notes,
            linkedUserId: null, // MVP: no link
            createdAt: new DateTimeImmutable(),
        );
    }
}
```

**DB Migration:**

```sql
CREATE TABLE mates
(
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owner_id       UUID REFERENCES users (id) ON DELETE CASCADE,
    name           VARCHAR(255) NOT NULL,
    notes          TEXT,
    linked_user_id UUID REFERENCES users (id), -- Expansion: link to user
    created_at     TIMESTAMP        DEFAULT NOW(),
    updated_at     TIMESTAMP,
    UNIQUE (owner_id, name)                    -- Unique names within directory
);
CREATE INDEX idx_mates_owner ON mates (owner_id);
CREATE INDEX idx_mates_linked ON mates (linked_user_id);
```

**Response for GET /api/mates:**

```json
{
    "items": [
        {
            "id": "uuid",
            "name": "Alice",
            "notes": "Neighbor, loves Carcassonne",
            "linkedUser": null,
            "stats": {
                "gamesPlayed": 45,
                "lastPlayedAt": "2025-01-15"
            }
        }
    ]
}
```

**Dependencies:** AUTH-001

---

### STATS-001: User's Top Games

**User Story:**
> As a user, I want to see my most played games.

**Acceptance Criteria:**

- [ ] GET `/api/stats/games` returns top games by play count
- [ ] Parameters: `?limit=10`, `?period=month|year|all`
- [ ] Returns: game info, playCount, lastPlayedAt
- [ ] Sorted by playCount DESC

**Technical Context:**

```
Bounded Context: Stats
Layers:
├── Domain/Stats/Services/GameStatsCalculator.php
├── Application/Handlers/Stats/GetTopGames/Query.php
└── Application/Handlers/Stats/GetTopGames/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/stats/games' => [
    'message' => Stats\GetTopGames\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "period": "month",
    "items": [
        {
            "game": {
                "id": "uuid",
                "name": "Carcassonne",
                "imageUrl": "https://..."
            },
            "playCount": 12,
            "lastPlayedAt": "2025-01-15"
        }
    ]
}
```

**SQL Query:**

```sql
SELECT g.*, COUNT(p.id) as play_count, MAX(p.started_at) as last_played
FROM games g
         JOIN plays p ON p.game_id = g.id
WHERE p.user_id = :userId
  AND p.started_at >= :periodStart
GROUP BY g.id
ORDER BY play_count DESC
LIMIT :limit
```

**Dependencies:** PLAYS-001

---

### REPORTS-001: Annual Report

**User Story:**
> As a user, I want to get a beautiful annual report.

**Acceptance Criteria:**

- [ ] GET `/api/reports/year/:year` generates report
- [ ] Includes: top games, top co-players, overall statistics
- [ ] Total play count for year
- [ ] Total play time (if started_at/finished_at exist)
- [ ] Most active month
- [ ] Comparison with previous year (if data exists)
- [ ] JSON format for UI display

**Technical Context:**

```
Bounded Context: Stats
Layers:
├── Domain/Stats/Services/YearReportGenerator.php
├── Application/Handlers/Stats/GetYearReport/Query.php
└── Application/Handlers/Stats/GetYearReport/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/reports/year/{year}' => [
    'message' => Stats\GetYearReport\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "year": 2025,
    "summary": {
        "totalPlays": 156,
        "uniqueGames": 42,
        "totalPlayTimeMinutes": 12450,
        "averagePlayTimeMinutes": 80
    },
    "topGames": [
        ...
    ],
    "topMates": [
        ...
    ],
    "monthlyActivity": [
        {
            "month": "2025-01",
            "plays": 15
        },
        {
            "month": "2025-02",
            "plays": 22
        }
    ],
    "comparison": {
        "previousYear": 2024,
        "playsChange": 23,
        "playsChangePercent": 17.3
    }
}
```

**Dependencies:** STATS-001, MATES-001

---

### API-001: OpenAPI Documentation

**User Story:**
> As an external developer, I want API documentation for integration.

**Acceptance Criteria:**

- [ ] Swagger/OpenAPI 3.0 specification
- [ ] Auto-generation from annotations or separate YAML
- [ ] Swagger UI available at `/api/docs`
- [ ] Request and response examples
- [ ] Error descriptions
- [ ] Authentication via Bearer token

**Technical Context:**

```
Files:
├── docs/api/openapi.yaml — specification
└── public/api-docs/ — Swagger UI assets
```

**Routes (config/routes.php):**

```php
// Documentation served statically via nginx or special middleware
'GET /api/docs' => [
    'static' => 'public/api-docs/index.html',
    'public' => true,
],
'GET /api/docs/openapi.yaml' => [
    'static' => 'docs/api/openapi.yaml',
    'public' => true,
],
```

**Dependencies:** All MVP API endpoints

---

### INFRA-001: Docker Environment

**User Story:**
> As a developer, I want to run the project with a single command.

**Acceptance Criteria:**

- [ ] `make init` initializes environment
- [ ] `make docker-up` starts containers
- [ ] Containers: php-fpm, nginx, postgres, redis
- [ ] Volumes for data persistence
- [ ] Healthchecks for all services

**Technical Context:**

```
Files:
├── docker-compose.yml
├── docker/
│   ├── php/Dockerfile
│   ├── nginx/default.conf
│   └── postgres/init.sql
└── Makefile
```

**docker-compose.yml:**

```yaml
services:
    app:
        build: ./docker/php
        volumes:
            - .:/var/www
        depends_on:
            - postgres
            - redis

    nginx:
        image: nginx:alpine
        ports:
            - "8080:80"
        volumes:
            - .:/var/www
            - ./docker/nginx:/etc/nginx/conf.d

    postgres:
        image: postgres:15-alpine
        environment:
            POSTGRES_DB: boardgamelog
            POSTGRES_USER: app
            POSTGRES_PASSWORD: secret
        volumes:
            - postgres_data:/var/lib/postgresql/data

    redis:
        image: redis:alpine
        volumes:
            - redis_data:/data

volumes:
    postgres_data:
    redis_data:
```

**Dependencies:** None (first task)

---

### INFRA-002: CI Pipeline

**User Story:**
> As a developer, I want automatic code checking on every push.

**Acceptance Criteria:**

- [ ] GitHub Actions workflow
- [ ] Steps: lint → psalm → deptrac → tests
- [ ] Composer dependencies caching
- [ ] Status badge in README

**Technical Context:**

```yaml
# .github/workflows/ci.yml
name: CI

on: [ push, pull_request ]

jobs:
    test:
        runs-on: ubuntu-latest

        services:
            postgres:
                image: postgres:15
                env:
                    POSTGRES_DB: test
                    POSTGRES_USER: test
                    POSTGRES_PASSWORD: test
                options: >-
                    --health-cmd pg_isready
                    --health-interval 10s
                    --health-timeout 5s
                    --health-retries 5

        steps:
            -   uses: actions/checkout@v4

            -   name: Setup PHP
                uses: shivammathur/setup-php@v2
                with:
                    php-version: '8.4'
                    extensions: pdo_pgsql, redis

            -   name: Cache Composer
                uses: actions/cache@v3
                with:
                    path: vendor
                    key: composer-${{ hashFiles('composer.lock') }}

            -   name: Install dependencies
                run: composer install --no-progress

            -   name: Run checks
                run: make scan
```

**Dependencies:** INFRA-001

---

## Phase 2: Expansion

### STATS-002: Win Statistics

**User Story:**
> As a user, I want to see my win percentage by games and overall.

**Acceptance Criteria:**

- [ ] GET `/api/stats/wins` returns win statistics
- [ ] Overall win percentage
- [ ] Win percentage per game
- [ ] Period filter
- [ ] Considers only plays where isWinner is defined

**Technical Context:**

```
Bounded Context: Stats
Layers:
├── Application/Handlers/Stats/GetWinStats/Query.php
└── Application/Handlers/Stats/GetWinStats/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/stats/wins' => [
    'message' => Stats\GetWinStats\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "overall": {
        "totalPlays": 100,
        "wins": 42,
        "winRate": 0.42
    },
    "byGame": [
        {
            "game": {
                "id": "uuid",
                "name": "Carcassonne"
            },
            "totalPlays": 20,
            "wins": 12,
            "winRate": 0.60
        }
    ]
}
```

**Dependencies:** STATS-001

---

### STATS-003: Co-Player Statistics

**User Story:**
> As a user, I want to see who I play with most often and who I win against.

**Acceptance Criteria:**

- [ ] GET `/api/stats/mates` returns co-player statistics
- [ ] Top co-players by joint play count
- [ ] Win rate against each co-player
- [ ] Favorite games with each co-player

**Technical Context:**

```
Bounded Context: Stats
Layers:
├── Application/Handlers/Stats/GetMateStats/Query.php
└── Application/Handlers/Stats/GetMateStats/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/stats/mates' => [
    'message' => Stats\GetMateStats\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "mates": [
        {
            "mate": {
                "id": "uuid",
                "name": "Alice"
            },
            "gamesPlayed": 45,
            "winRateAgainst": 0.55,
            "favoriteGame": {
                "name": "Carcassonne",
                "count": 12
            }
        }
    ]
}
```

**Dependencies:** STATS-002, MATES-001

---

### STATS-004: Activity Trends

**User Story:**
> As a user, I want to see a graph of my gaming activity by month.

**Acceptance Criteria:**

- [ ] GET `/api/stats/trends` returns data for graph
- [ ] Parameters: `?period=year` (last 12 months)
- [ ] Returns: month, play count, unique games

**Technical Context:**

```
Bounded Context: Stats
Layers:
├── Application/Handlers/Stats/GetTrends/Query.php
└── Application/Handlers/Stats/GetTrends/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/stats/trends' => [
    'message' => Stats\GetTrends\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Response:**

```json
{
    "months": [
        {
            "month": "2025-01",
            "plays": 15,
            "uniqueGames": 8
        },
        {
            "month": "2024-12",
            "plays": 22,
            "uniqueGames": 12
        }
    ]
}
```

**Dependencies:** STATS-001

---

### MATES-002: Link Co-Player to User (Expansion)

**User Story:**
> As a user, I want to link a directory record to a registered user for more accurate statistics.

**Acceptance Criteria:**

- [ ] PUT `/api/mates/:id/link` links co-player to user
- [ ] Accepts: `{userId}` or `{email}` of registered user
- [ ] Requires confirmation from target user
- [ ] After linking, statistics are combined
- [ ] DELETE `/api/mates/:id/link` unlinks user

**Technical Context:**

```
Bounded Context: Mates
Layers:
├── Domain/Mates/Events/MateLinkRequested.php
├── Domain/Mates/Events/MateLinkConfirmed.php
├── Application/Handlers/Mates/LinkMate/Command.php
├── Application/Handlers/Mates/LinkMate/Handler.php
├── Application/Handlers/Mates/ConfirmLink/Command.php
├── Application/Handlers/Mates/ConfirmLink/Handler.php
├── Application/Handlers/Mates/UnlinkMate/Command.php
└── Application/Handlers/Mates/UnlinkMate/Handler.php
```

**Routes (config/routes.php):**

```php
'PUT /api/mates/{id}/link' => [
    'message' => Mates\LinkMate\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
'POST /api/mates/{id}/link/confirm' => [
    'message' => Mates\ConfirmLink\Command::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
'DELETE /api/mates/{id}/link' => [
    'message' => Mates\UnlinkMate\Command::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**DB Migration:** Uses existing `linked_user_id` field in `mates` table

**Dependencies:** MATES-001

---

### SYNC-001: Import Plays from BGG

**User Story:**
> As a BGG user, I want to import play history from BoardGameGeek.

**Acceptance Criteria:**

- [ ] POST `/api/sync/bgg/import` starts import
- [ ] Requires bgg_username specified in profile
- [ ] Imports plays for specified period
- [ ] Creates games if not in local DB
- [ ] Creates mate records for new players
- [ ] Skips duplicates (by bgg_play_id)
- [ ] Returns statistics: imported, skipped, errors

**Technical Context:**

```
Bounded Context: Sync
Layers:
├── Domain/Sync/Services/PlayImporter.php — Port
├── Application/Handlers/Sync/ImportFromBgg/Command.php
├── Application/Handlers/Sync/ImportFromBgg/Handler.php
└── Infrastructure/Sync/Bgg/BggPlayImporter.php — Adapter
```

**Routes (config/routes.php):**

```php
'POST /api/sync/bgg/import' => [
    'message' => Sync\ImportFromBgg\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
```

**BGG API:**

```
GET https://boardgamegeek.com/xmlapi2/plays?username=xxx&mindate=2024-01-01
```

**Dependencies:** GAMES-001, PLAYS-001, MATES-001

---

### SYNC-002: Export Plays to BGG

**User Story:**
> As a user, I want to export my plays back to BGG.

**Acceptance Criteria:**

- [ ] POST `/api/sync/bgg/export` exports unsynced plays
- [ ] Requires BGG credentials (or OAuth in future)
- [ ] Updates sync_status after successful export
- [ ] Returns: exported, failed, already_synced

**Technical Context:**

```
Bounded Context: Sync
Layers:
├── Domain/Sync/Services/PlayExporter.php — Port
├── Application/Handlers/Sync/ExportToBgg/Command.php
├── Application/Handlers/Sync/ExportToBgg/Handler.php
└── Infrastructure/Sync/Bgg/BggPlayExporter.php — Adapter
```

**Routes (config/routes.php):**

```php
'POST /api/sync/bgg/export' => [
    'message' => Sync\ExportToBgg\Command::class,
    'interceptors' => ['auth', 'denormalization', 'validation'],
    'public' => false,
],
```

**Note:** BGG API for play recording requires cookie authentication. May require separate microservice or headless
browser.

**Dependencies:** SYNC-001

---

## Phase 3: Scaling

### SOCIAL-001: Activity Feed

**User Story:**
> As a user, I want to see recent plays from my co-players.

**Acceptance Criteria:**

- [ ] GET `/api/feed` returns co-player activity with linked users
- [ ] Cursor pagination (for real-time updates)
- [ ] Event types: new_play, achievement
- [ ] Only from linked mates with visibility >= friends

**Technical Context:**

```
Bounded Context: Social
Layers:
├── Application/Handlers/Social/GetFeed/Query.php
└── Application/Handlers/Social/GetFeed/Handler.php
```

**Routes (config/routes.php):**

```php
'GET /api/feed' => [
    'message' => Social\GetFeed\Query::class,
    'interceptors' => ['auth', 'denormalization'],
    'public' => false,
],
```

**Dependencies:** MATES-002, PLAYS-001

---

### GAMES-003: Search Migration to Elasticsearch

**User Story:**
> As a system, I want faster and more relevant full-text game search.

**Acceptance Criteria:**

- [ ] Elasticsearch index for games
- [ ] Data migration from PostgreSQL
- [ ] Sync on new game addition
- [ ] Fallback to PostgreSQL on ES unavailability
- [ ] Improved scoring for relevance

**Technical Context:**

```
Layers:
├── Infrastructure/Search/ElasticsearchGames.php — new Searchable implementation
├── Infrastructure/Sync/ElasticsearchIndexer.php — synchronization
└── config/elasticsearch.php
```

**Dependencies:** GAMES-001

---

### PERF-001: Async Runtime

**User Story:**
> As a system, I want to handle more requests with fewer resources.

**Acceptance Criteria:**

- [ ] Migration to RoadRunner or Swoole
- [ ] Compatibility with existing code preserved
- [ ] Benchmarks: +50% RPS with same resources
- [ ] Graceful degradation on issues

**Technical Context:**

Per ADR-008, migration includes:

1. Replace php-fpm with RoadRunner
2. Adapt Doctrine for long-running processes
3. Memory leak management
4. Docker configuration update

**Dependencies:** All previous tasks

---

## Priorities and Execution Order

### MVP Critical Path:

```
INFRA-001 (Docker)
    ↓
┌───────────────────────────────────────────────────────┐
│ CORE-001 (Validation/Serialization)                   │
│     ↓                                                 │
│ CORE-004 (API Response Contracts)                     │
│     ↓                                                 │
│ CORE-002 (PasswordHasher)                             │
│     ↓                                                 │
│ CORE-003 (Mediator + ApiAction)                       │
└───────────────────────────────────────────────────────┘
    ↓
AUTH-001 (Register + Email Confirm) → AUTH-002 (Login) → AUTH-003 (Refresh) → AUTH-004 (Interceptors)
    ↓
MATES-001 (Co-players Directory)
    ↓
GAMES-001 (Search) → GAMES-002 (Details)
    ↓
PLAYS-001 (Create) → PLAYS-002 (List) → PLAYS-003 (View) → PLAYS-004 (Update) → PLAYS-005 (Delete)
    ↓
STATS-001 (Top Games) → REPORTS-001 (Year Report)
    ↓
API-001 (OpenAPI Docs)
    ↓
INFRA-002 (CI)
```

### Recommended Sprints:

**Sprint 0:** INFRA-001, CORE-001, CORE-004, CORE-002, CORE-003

**Sprint 1:** AUTH-001, AUTH-002, AUTH-003, AUTH-004

**Sprint 2:** MATES-001, GAMES-001, GAMES-002

**Sprint 3:** PLAYS-001, PLAYS-002, PLAYS-003

**Sprint 4:** PLAYS-004, PLAYS-005, STATS-001

**Sprint 5:** REPORTS-001, API-001, INFRA-002, bugfixes, refactoring

---

## Changes from Version 1.0

### AUTH-001

- Added passkey (WebAuthn) registration
- Email confirmed via magic link
- Added validation/serialization components as prerequisite
- Added PasswordHasher contract
- Integration with league/oauth2-server

### AUTH-002

- JWT no longer **contains email** — only userId
- Tokens are **NOT stored in DB** — signature validation only
- Removed refresh_tokens table

### AUTH-004

- Renamed to "Authentication and Authorization Interceptors"
- `AuthInterceptor` and `AuthorizationInterceptor` now in **Presentation layer** (not Application)
- Integration with CORE-003 architecture (Interceptors pipeline)
- Interceptors implement `Interceptor` contract, not `MessageMiddleware`
- Added dependency on CORE-003

### GAMES-001

- Using composer package for BGG API
- Phased migration: PostgreSQL → Redis → Elasticsearch
- Using `Searchable` contract
- Support for requesting only specific fields

### PLAYS-001

- `gameId` now **optional**
- Removed `playedAt` field
- Added `startedAt` and `finishedAt` fields (both optional)
- Validation: `startedAt <= finishedAt`
- Added `Visibility` enum for visibility levels

### PLAYS-003

- Added visibility levels: private, **link**, friends, registered, public
- New `link` level — access by link for any user (including unauthenticated)
- Difference between `link` and `public`: `link` plays are not indexed and not displayed in public lists
- On visibility mismatch — **403 Forbidden** (not 404)

### PLAYS-004

- `gameId` now **can be changed and removed**

### MATES-001

- Completely reworked: own co-players directory without userId link
- Mates — this is owner's directory, not connection between users
- User linking moved to Expansion (MATES-002)

### API-001

- Moved to **MVP** (was in Phase 3)

### REPORTS-001

- Moved to **MVP** (was in Phase 3)

### New Tasks

- CORE-001: Validation, denormalization, and serialization components
- CORE-002: Password hashing contract and component
- CORE-003: Mediator pattern, unified API entry point, two-level middleware system (Interceptors + Aspects)
- CORE-004: API Response Contracts (success/error response format)
- MATES-002: Link co-player to user (Expansion)
- GAMES-003: Search migration to Elasticsearch (Phase 3)
