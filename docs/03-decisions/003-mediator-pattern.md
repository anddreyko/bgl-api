# Mediator Pattern (Message Bus)

## Date: 2024-01-15

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

When implementing Clean Architecture, a question arises: how to organize communication between Presentation Layer and
Application Layer? Controllers should not directly create dependencies on Handlers to maintain loose coupling.

**Challenges:**

- Presentation Layer should not know about specific Handler implementations
- Need to support different message types (Command, Query, Event)
- Unified way to handle cross-cutting concerns (logging, transactions) required
- Must be easy to add middleware

**Requirements:**

- Layer decoupling
- Unified interface for sending messages
- Future asynchronous processing support
- Middleware addition capability (logging, transactions)

### Considered Options

#### Option 1: Direct Dependency Injection

Controllers receive Handlers through DI container and call them directly.

**Pros:**

- Simple implementation
- Clear call flow
- IDE support and autocomplete

**Cons:**

- Strong coupling between controllers and Handlers
- Difficult to add common middleware processing
- Violates Open/Closed principle when adding new cross-cutting concerns
- Presentation Layer knows about specific Application Layer classes

#### Option 2: Mediator Pattern (Message Bus)

All messages are sent through a unified bus that routes them to corresponding Handlers.

**Pros:**

- Complete decoupling between sender and receiver
- Single point for adding middleware
- Easily extensible architecture
- Support for different transports (sync, async)

**Cons:**

- Additional abstraction layer
- Debugging complexity (no direct calls visible)
- Need to configure message mapping
- IDE less effective at showing component relationships

#### Option 3: Event-Driven Architecture (Events Only)

All communication through events without synchronous commands.

**Pros:**

- Maximum decoupling
- Natural asynchrony support
- Scales well

**Cons:**

- Difficulty getting operation result
- Eventual consistency by default
- Distributed event debugging complexity
- Excessive for synchronous operations

### Decision

**Decision:** Mediator Pattern adopted (Option 2) with Tactician library

**Reason for choice:**

1. **Layer decoupling** — Presentation Layer sends Message without knowing about Handler
2. **Middleware** — easy to add transactions, logging, validation
3. **CQS** — natural separation into Command and Query Bus
4. **Testability** — MessageBus can be easily mocked

### Consequences

**Positive:**

- Controllers are maximally thin — only Message formation and Bus dispatch
- Transactions, logging, metrics are added in one place (middleware)
- Easy to switch to asynchronous processing for heavy operations
- Clear separation: Command changes state, Query only reads

**Negative/Risks:**

- Debugging requires tracing path through middleware
- Message → Handler mapping configuration requires attention
- Type safety can be lost with improper usage
- Additional classes for each message

### Notes

**Message Structure:**

```
src/Application/Handlers/
├── Auth/
│   ├── IssueToken/
│   │   ├── Command.php      # Message
│   │   └── Handler.php      # Handler
│   └── RevokeToken/
│       ├── Command.php
│       └── Handler.php
├── Plays/
│   ├── CreatePlay/
│   │   ├── Command.php
│   │   └── Handler.php
│   └── GetPlayHistory/
│       ├── Query.php
│       └── Handler.php
```

**Message Contracts:**

```php
// src/Core/Messages/Message.php
interface Message {}

// src/Core/Messages/Command.php
interface Command extends Message {}

// src/Core/Messages/Query.php
interface Query extends Message {}

// src/Core/Messages/Event.php
interface Event extends Message {}
```

**Controller Usage Example:**

```php
final class CreatePlayAction
{
    public function __construct(
        private MessageBus $bus,
    ) {}

    public function __invoke(Request $request): Response
    {
        $command = new CreatePlayCommand(
            gameId: $request->get('game_id'),
            date: $request->get('date'),
            players: $request->get('players'),
        );

        $playId = $this->bus->handle($command);

        return new JsonResponse(['id' => $playId], 201);
    }
}
```

**Middleware Pipeline:**

```
Request → Logging → Transaction → Validation → Handler → Response
```

**Implementation:** Uses `league/tactician` library with custom locator for resolving Handlers from DI container.

**References:**

- [Tactician Command Bus](https://tactician.thephpleague.com/)
- [Mediator Pattern](https://refactoring.guru/design-patterns/mediator)
- [CQS by Martin Fowler](https://martinfowler.com/bliki/CommandQuerySeparation.html)
