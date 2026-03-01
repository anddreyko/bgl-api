# Code Conventions

Detailed coding conventions for BoardGameLog API. For a concise summary, see [AGENTS.md](../../AGENTS.md) section 7.

---

## 1. Value Objects

Immutable wrappers around primitive values with validation in the constructor.

**Rules:**

- `final readonly class`
- Constructor validates and throws `\InvalidArgumentException` on invalid input
- Methods: `getValue()`, `isNull()` (when nullable), `__toString()` (when `Stringable`)
- No setters. Return new instance for transformations
- Located in `Core/ValueObjects/` (shared) or `Domain/{Context}/ValueObjects/` (context-specific)

```php
// DO
final readonly class Email implements \Stringable
{
    public function __construct(
        private ?string $value = null,
    ) {
        if ($this->value !== null && filter_var($this->value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException(
                sprintf('Invalid email format: "%s"', $this->value),
            );
        }
    }

    public function isNull(): bool
    {
        return $this->value === null;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ?? '';
    }
}

// DON'T
class Email
{
    public string $value; // mutable, no validation, public property
    public function setValue(string $v): void { $this->value = $v; } // setter
}
```

---

## 2. Entities

Rich domain objects with business logic. Identity via `Uuid`. State changes through named methods.

**Rules:**

- Private (or public for `id`) constructor + static factory method (`create()`, `register()`)
- Business methods instead of setters (`update()`, `finalize()`, `softDelete()`)
- Named `DomainException` subclasses for invariant violations (see section 8)
- Private/readonly properties with getters
- No dependencies except Enums and Value Objects
- Located at `Domain/{Context}/` root (aggregate root) or `Domain/{Context}/{ChildEntity}/` (child entities)

```php
// DO
final class Play
{
    private function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private PlayStatus $status,
        private readonly DateTime $startedAt,
        private ?DateTime $finishedAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        DateTime $startedAt,
    ): self {
        return new self($id, $userId, PlayStatus::Draft, $startedAt, null);
    }

    public function finalize(DateTime $finishedAt): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new PlayNotDraftException();
        }
        $this->status = PlayStatus::Published;
        $this->finishedAt = $finishedAt;
    }

    public function getId(): Uuid { return $this->id; }
    public function getStatus(): PlayStatus { return $this->status; }
}

// DON'T
final class Play
{
    public function setStatus(string $status): void { ... }        // setter, string instead of enum
    public function finalize(): void { throw new \DomainException('...'); } // bare DomainException
}
```

---

## 3. Enums

Backed string enums for serialization and type safety.

**Rules:**

- `enum Name: string` with backing type
- Lowercase backing values (kebab-case or snake_case)
- Docblock for each case when meaning is not obvious
- Use in method parameters instead of raw strings
- Located alongside the Entity they belong to

```php
// DO
enum Visibility: string
{
    /** Only the play owner can see */
    case Private = 'private';

    /** Anyone with a direct link can see */
    case Link = 'link';

    /** Visible to everyone */
    case Public = 'public';
}

// DON'T
const VISIBILITY_PRIVATE = 'private';  // magic strings
function setVisibility(string $v): void { ... } // string instead of enum
```

---

## 4. Commands and Queries

Messages dispatched through the Mediator (MessageBus). Carry input data for a use case.

**Rules:**

- `final readonly class` with public properties (property promotion)
- Implements `Message` with generic result type: `@implements Message<Result>`
- Type-safe parameters: `non-empty-string` in docblock, native types in signature
- Commands mutate state, Queries read state
- Located in `Application/Handlers/{Context}/{UseCase}/`

```php
// DO
/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $userId
     */
    public function __construct(
        public string $userId,
        public string $name,
        public ?string $notes = null,
    ) {
    }
}

// DON'T
class CreateMateCommand  // not final, not readonly
{
    private string $userId;  // private properties on a message
    public function getUserId(): string { return $this->userId; }
}
```

---

## 5. Results

Type-safe return objects from Handlers. Can contain Value Objects, entities, or other typed structures for mediator pipeline composition.

**Rules:**

- `final readonly class` with public properties
- Can contain VO, typed DTOs, or other domain objects (not only primitives)
- Serialization to JSON primitives happens in Presentation layer, not here
- Located alongside their Command/Query in `Application/Handlers/{Context}/{UseCase}/`

```php
// DO -- type-safe Result
final readonly class Result
{
    /**
     * @param list<MateItem> $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}

// DO -- Result with VO
final readonly class Result
{
    public function __construct(
        public Uuid $id,
        public Email $email,
        public UserStatus $status,
    ) {
    }
}

// DON'T -- primitives-only, mapping in handler
final readonly class Result
{
    public function __construct(
        public string $id,       // should be Uuid
        public string $email,    // should be Email
        public string $status,   // should be UserStatus enum
    ) {
    }
}
```

---

## 6. Handlers (MessageHandler)

Universal generic contract for processing messages. One handler per use case.

**Rules:**

- `final readonly class implements MessageHandler`
- Generic signature: `@implements MessageHandler<Result, Command>`
- Method: `__invoke(Envelope $envelope): Result`
- Dependencies via constructor injection (interfaces, not implementations)
- Extract message: `$message = $envelope->message`
- `MessageHandler<R, M>` is a universal contract, not limited to use-case handlers
- Located in `Application/Handlers/{Context}/{UseCase}/`

```php
// DO
/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates, // interface, not DoctrineMates
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $mate = Mate::create(
            new Uuid($command->id),
            new Uuid($command->userId),
            $command->name,
            $command->notes,
            new DateTime('now'),
        );

        $this->mates->add($mate);

        return new Result(id: $mate->getId());
    }
}

// DON'T
class MateHandler // not final, not readonly, generic name
{
    public function handle(Command $command): array { ... } // returns array, wrong signature
}
```

---

## 7. Repositories (Collections)

Repositories are **domain collections**. Interface in Domain, implementations in Infrastructure.

**Rules:**

- Interface extends `Repository<Entity>` (and optionally `Searchable`)
- Returns: Entity, Value Object, or scalar only when absolutely necessary (e.g. `count(): int`)
- Never returns DTO or raw arrays of primitives
- No business logic in repositories
- Generics: `@extends Repository<Entity>`, `@template TEntity of object`
- Domain interface in `Domain/{Context}/Entities/`
- Doctrine implementation in `Infrastructure/Persistence/Doctrine/`
- InMemory implementation in `Infrastructure/Persistence/InMemory/` (for tests)

```php
// DO -- Domain interface (collection contract)
/**
 * @extends Repository<Mate>
 */
interface Mates extends Repository, Searchable
{
    public function findByUserAndName(Uuid $userId, string $name): ?Mate;

    /** @return list<Mate> */
    public function findAllByUser(Uuid $userId, int $limit, int $offset): array;

    public function countByUser(Uuid $userId): int; // scalar OK for count
}

// DON'T
interface MatesRepository // suffix "Repository" -- redundant, it IS a collection
{
    /** @return array<string, mixed> */
    public function findAllAsArray(): array; // returns raw arrays, not entities
}
```

---

## 8. Exceptions

Hybrid hierarchy: Core exceptions (RuntimeException) for infrastructure/cross-cutting concerns, Domain exceptions (DomainException) for business rule violations.

**Rules:**

- **Never** throw bare `\DomainException` or `\RuntimeException` -- always use a named subclass
- Core exceptions extend `\RuntimeException`: `NotFoundException`, `AccessDeniedException`, `AuthenticationException`
- Domain exceptions extend `\DomainException`: per-context named classes for business rules
- Default message via `protected $message` property
- `final` for leaf exceptions, non-final for base classes that will be extended
- Located in `Core/Exceptions/` (core) or `Domain/{Context}/Exceptions/` (domain)

```php
// DO -- Core exception (infrastructure concern)
final class NotFoundException extends \RuntimeException
{
    protected $message = 'Resource not found';
}

// DO -- Domain exception (business rule)
final class PlayNotDraftException extends \DomainException
{
    protected $message = 'Play can only be modified in draft status';
}

// DO -- Domain exception hierarchy
class AuthenticationException extends \RuntimeException
{
    protected $message = 'Authentication failed';
}

final class InvalidCredentialsException extends AuthenticationException
{
    protected $message = 'Invalid credentials';
}

// DON'T
throw new \DomainException('Play can only be modified in draft status'); // bare exception
throw new \RuntimeException('Not found');                                // bare exception
```

---

## 9. RESTful API

Strict RESTful conventions for all HTTP endpoints.

**Rules:**

- URL structure: `/v1/{resource}/{id}` (plural nouns, no verbs in URLs)
- Standard HTTP methods: GET (read), POST (create), PUT (full update), PATCH (partial update), DELETE
- Standard status codes: 200 (OK), 201 (Created), 204 (No Content), 400 (Bad Request), 401 (Unauthorized), 403 (Forbidden), 404 (Not Found), 422 (Validation Error), 500 (Server Error)
- Response envelope: `{ "code": 0, "data": {...} }` for success, `{ "code": 1, "message": "...", "errors": {...} }` for error
- Pagination via query params: `?page=1&size=20&sort=name&order=asc`
- Entity-to-JSON mapping happens in Presentation layer (middleware/handler), not in Application handlers
- Logging strictly by categories via Logging aspect (unified, structured)

```
// DO
GET    /v1/mates          -- list mates (paginated)
GET    /v1/mates/{id}     -- get mate by id
POST   /v1/mates          -- create mate
PUT    /v1/mates/{id}     -- update mate
DELETE /v1/mates/{id}     -- delete mate

POST   /v1/auth/sign-in   -- login (exception: auth actions use verbs)
POST   /v1/auth/sign-up   -- register
POST   /v1/auth/sign-out  -- logout

// DON'T
POST   /v1/createMate     -- verb in URL
GET    /v1/getMate?id=123  -- id in query, not path
```

---

## 10. No Arrays in Public Contracts

No PHP arrays in public APIs (method parameters, return types, constructor properties). Use typed objects instead.

**Rules:**

- **ClassMap** for key-value containers keyed by `class-string<T>` (type-safe object registry)
- **Iterator / IteratorAggregate** for sequences (lazy, typed, composable)
- **Typed collections** (extending `ArrayCollection` or custom) for domain collections
- **readonly class** (DTO/VO) for structured data instead of `array<string, mixed>`
- `array` is allowed only inside `private` implementation details
- Exceptions to this rule are made only by the project owner personally

```php
// DO -- ClassMap for type-safe object registry
final readonly class ClassMap
{
    /** @var array<class-string, object> */
    private array $values;

    public function __construct(object ...$values)
    {
        $this->values = array_combine(
            keys: array_map(get_class(...), $values),
            values: $values,
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function get(string $class): object
    {
        return $this->values[$class] ?? throw new \OutOfBoundsException();
    }
}

// DO -- typed collection for domain lists
final class PlayerCollection extends ArrayCollection implements Players
{
    // ...
}

// DO -- Iterator for sequences
/**
 * @implements \IteratorAggregate<int, Mate>
 */
final class MateList implements \IteratorAggregate
{
    // ...
}

// DO -- readonly class instead of array shape
final readonly class PaginatedResult
{
    /**
     * @param Players $items
     */
    public function __construct(
        public Players $items,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}

// DON'T
/** @return array<string, mixed> */
public function toArray(): array { ... }

/** @param array{name: string, email: string} $data */
public function create(array $data): void { ... }

/** @return list<array{id: string, name: string}> */
public function findAll(): array { ... }
```

---

## 11. Type System

Strict typing with generics via docblocks for Psalm static analysis.

**Rules:**

- `declare(strict_types=1)` in every PHP file
- `final` for all concrete classes (no unintended inheritance)
- `readonly` for immutable classes and properties
- Constructor property promotion everywhere
- Named arguments when constructing objects with 3+ parameters
- Generics via docblocks: `@template T`, `@extends Repository<Entity>`, `@implements Message<Result>`
- `non-empty-string` in docblock for strings that must not be empty
- No `mixed` unless absolutely necessary (external library boundaries)
- Interfaces without prefix/suffix: `Mates`, not `MatesInterface` or `IMates`
- `#[\Override]` attribute on all methods that override parent/interface

```php
// DO
/**
 * @template TEntity of object
 * @implements Repository<TEntity>
 */
abstract class DoctrineRepository implements Repository
{
    /** @return class-string<TEntity> */
    abstract public function getType(): string;

    #[\Override]
    public function add(object $entity): void { ... }
}

// DO -- named arguments
$mate = Mate::create(
    id: new Uuid($command->id),
    userId: new Uuid($command->userId),
    name: $command->name,
    notes: $command->notes,
    createdAt: new DateTime('now'),
);

// DON'T
interface IMatesInterface { ... }  // prefix + suffix
class MateService { ... }         // not final
public function process(mixed $data): mixed { ... } // mixed everywhere
```

---

## 12. Design Patterns

Recognized structural patterns used across the codebase. Follow these when adding new code.

### Decorator

Wraps existing behavior with additional concerns without modifying the original.

- **Aspects** (`Logging`, `Transactional`) decorate handlers via MessageBus middleware pipeline
- **InterceptorPipeline** decorates HTTP request (auth, tracing)
- **TrimStringsMiddleware** decorates request body (PSR-15 middleware)

```
MessageBus pipeline:
  Logging → Transactional → CommandHandlerMiddleware → Handler

HTTP pipeline:
  TrimStringsMiddleware → ... → InterceptorPipeline(Auth) → ApiAction
```

### Bridge (Ports & Adapters)

Ports (interfaces) in Domain/Core, implementations in Infrastructure. Decouples business logic from external libraries.

| Port (Domain/Core)       | Adapter (Infrastructure)                            |
|--------------------------|-----------------------------------------------------|
| `Tokenizer`              | `JwtTokenizer`, `PlainTokenizer`                    |
| `FilterVisitor`          | `DoctrineFilter`, `InMemoryFilter`                  |
| `Mates`, `Users`, ...    | `DoctrineMates`, `InMemoryMates`, ...               |
| `PlayersFactory`         | `DoctrinePlayersFactory`, `InMemoryPlayersFactory`   |
| `PasswordHasher`         | `BcryptPasswordHasher`                              |
| `UuidGenerator`          | `RamseyUuidGenerator`                               |

### Adapter

Adapts external library APIs to domain contracts. Unlike Bridge, the focus is on translating a specific library interface.

| Domain contract          | Library adapter                                      |
|--------------------------|------------------------------------------------------|
| `Dispatcher`             | `TacticianDispatcher` (Tactician CommandBus)          |
| `Aspect`                 | `TacticianWrapMiddleware` (Tactician Middleware)       |
| `Deserializer`           | `MappedDeserializer` (CuyZ/Valinor)                  |
| `RequestValidator`       | `LeagueRequestValidator` (League OpenAPI)             |
| `Players`                | `PlayerCollection` (Doctrine ArrayCollection)         |
| `UuidGenerator`          | `RamseyUuidGenerator` (Ramsey UUID)                   |

### Proxy

Stands in for another object, controlling access or deferring initialization.

- **Doctrine lazy-loading** for entity collections (`PersistentCollection` proxies `Players`)
- **Test doubles** substitute real implementations: `FakePasskeyVerifier`, `PlainTokenizer`

### RAII (Resource Acquisition Is Initialization)

Resource lifecycle managed automatically; callers are unaware of setup/teardown.

- **Transactional aspect** manages DB transaction lifecycle (begin/commit/rollback) -- handlers never call transaction methods directly
- If handler succeeds, aspect commits; if exception is thrown, aspect rolls back

---

## 13. Domain Context Structure

Flat structure within each Bounded Context. Aggregate root and its belongings at the context root, child entities in their own subdirectories.

**Rules:**

- **No domain services.** Entity invariants live in Entity, collection invariants in Repository, cross-context interaction via Domain Events
- **No `Entities/` subdirectory.** Aggregate root lives at the context root
- **No `Exceptions/` subdirectory.** Domain exceptions live alongside the entity they protect
- Child entities of the aggregate get their own subdirectory
- Repository interface (collection) for the aggregate root lives at the context root
- Handlers (Application layer) are the only way to process messages (Command, Query, Event)

```
src/Domain/Plays/
├── Play.php                  # aggregate root
├── Plays.php                 # collection interface (repository)
├── PlayStatus.php            # enum of aggregate
├── Visibility.php            # enum of aggregate
├── PlayNotDraftException.php # exception of aggregate
└── Player/
    ├── Player.php            # child entity
    ├── Players.php           # collection interface
    ├── PlayersFactory.php    # factory interface
    └── EmptyPlayers.php

src/Domain/Mates/
├── Mate.php                  # aggregate root (single entity)
├── Mates.php                 # collection interface
└── MateAlreadyExistsException.php

src/Domain/Profile/
├── User.php                  # aggregate root
├── Users.php                 # collection interface
├── UserId.php                # VO of aggregate
├── UserStatus.php            # enum of aggregate
├── UserAlreadyExistsException.php
└── Passkey/
    ├── Passkey.php           # child entity
    ├── Passkeys.php          # collection interface
    ├── PasskeyChallenge.php
    └── PasskeyChallenges.php
```

### Cross-context interaction

- **Never** import from another Bounded Context directly
- Cross-context communication only via Domain Events through the MessageBus
- If a handler needs data from another context, it dispatches a Query/Event, never injects a foreign repository

---

## 14. Persistence (ORM-agnostic)

Persistence is ORM-agnostic. Domain defines ports (Repository, Searchable, Filter), Infrastructure provides adapters. The concrete ORM is swappable via DI configuration.

**Rules:**

- **Domain entities have zero ORM dependencies.** No attributes, annotations, or base classes from any ORM. Entity = plain PHP object
- **Mapping is external to Entity.** Mapping configuration lives in Infrastructure, never on the Entity itself
- **Repository = Collection port.** Domain defines the collection interface, Infrastructure implements it for a specific ORM or raw SQL
- **ORM is an implementation detail.** Swappable through DI container without touching Domain or Application layers
- **Lazy loading and relations must be supported.** The chosen persistence adapter must support lazy loading and relation management -- raw SQL without a mapper is not acceptable
- **Batch operations required.** Repository must provide batch methods (`findByIds()`) to prevent N+1 queries
- **InMemory adapters for unit tests only.** Integration tests use real database. InMemory implementations exist for isolated unit testing of complex domain logic

```
Port (Domain/Core)          Adapter (Infrastructure)
─────────────────           ───────────────────────────
Repository<T>          -->  DoctrineRepository<T>  (or CycleRepository<T>, etc.)
Searchable             -->  DoctrineFilter         (or any query builder adapter)
Filter / FilterVisitor -->  DoctrineFilter         (or any visitor implementation)
Transactor             -->  DoctrineTransactor     (or any transaction manager)
                            └── UnitOfWork         (ORM-internal: change tracking, flush/sync)
PlayersFactory         -->  DoctrinePlayersFactory (or any collection factory)
```

### What stays in Domain

- Repository interfaces (collection contracts)
- Filter/Searchable contracts
- Factory interfaces
- Entity, VO, Enum -- pure PHP, no ORM deps

### What stays in Infrastructure

- ORM mapping (external config, not on Entity)
- Repository implementations (SQL, ORM queries)
- Custom types (UuidType, EmailType, DateTimeType)
- Transaction management (Transactor adapter)
- Unit of Work (ORM-level change tracking and flush/sync -- implementation detail, not a domain port)
- Collection adapters (lazy-loadable)
