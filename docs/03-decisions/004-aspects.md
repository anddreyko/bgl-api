# Aspect-Oriented Programming (AOP)

## Date: 2024-01-20

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

The system has cross-cutting concerns that apply to many methods and classes: transaction management, logging, caching,
audit. Placing this code directly in business methods leads to duplication and pollution of business logic.

**Challenges:**

- Transaction logic must apply to all Handlers that change state
- Method entry/exit logging is required for debugging and monitoring
- Query result caching should be declarative
- Performance metrics are needed for all critical paths

**Requirements:**

- Separation of cross-cutting functionality from business logic
- Centralized aspect configuration
- Type safety and PHP 8.4 compatibility
- Minimal performance impact

### Considered Options

#### Option 1: Manual Implementation (Decorator Pattern)

Manual creation of decorators for each cross-cutting concern.

**Pros:**

- Full control over implementation
- Clear code structure
- No external libraries required
- Easy to debug

**Cons:**

- Much boilerplate code
- Need to create decorator for each interface
- Difficult to combine multiple aspects
- Risk of desynchronization with original interface

#### Option 2: Attribute-Based AOP

Using AOP library with PHP attributes on handler classes.

**Pros:**

- Declarative application via attributes
- Visible at class level

**Cons:**

- Hidden magic in attribute processing
- Scattered configuration across codebase
- IDE less effective at showing aspect application
- Requires AOP framework with weaving

#### Option 3: Middleware Pipeline Configuration

Aspects as middleware classes configured in DI container, applied through MessageBus pipeline.

**Pros:**

- Centralized configuration in one place
- Clear execution order defined in config
- Standard middleware pattern (no magic)
- Easy to test each middleware in isolation
- Full IDE support and debugging

**Cons:**

- Configuration separate from handler code
- Need to check config to understand applied aspects

---

### Decision

**Decision:** Middleware Pipeline Configuration adopted (Option 3)

Aspects are implemented as middleware classes that implement `MessageMiddleware` interface. All aspect configuration
happens in DI container where the MessageBus pipeline is assembled.

**Reason for choice:**

1. **Centralization** — all aspect configuration in one place (DI config)
2. **Transparency** — clear middleware pipeline, no hidden magic
3. **Testability** — each middleware can be tested in isolation
4. **Standard Pattern** — follows well-known middleware pattern
5. **IDE Support** — full debugging and navigation support

---

### Consequences

**Positive:**

- Handlers contain only business logic, without technical code
- Middleware order is explicit in configuration
- Easy to add/remove/reorder middleware
- Each middleware is independently testable
- No compile-time weaving or proxy generation needed

**Negative/Risks:**

- Need to check DI config to see which aspects apply
- Configuration separate from handler code
- Must maintain middleware order manually

---

### Notes

**Middleware Interface:**

```php
// src/Core/Messages/MessageMiddleware.php
interface MessageMiddleware
{
    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed;
}
```

**Logging Middleware Implementation:**

```php
// src/Application/Aspects/Logging.php
final readonly class Logging implements MessageMiddleware
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed
    {
        $this->logger->info(
            'Start handle {message_class}',
            ['message_class' => $envelope->message::class]
        );

        try {
            $result = $handler($envelope);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error handle {message_class}',
                ['message_class' => $envelope->message::class, 'exception' => $exception]
            );
            throw $exception;
        }

        $this->logger->info(
            'Finish handle {message_class}',
            ['message_class' => $envelope->message::class]
        );

        return $result;
    }
}
```

**MessageBus Pipeline Configuration (Tactician example):**

```php
// Middleware configured in DI container
$commandBus = new CommandBus([
    // Aspects applied in order (first to last)
    new TacticianWrapMiddleware(Logging::class, $container),
    new TacticianWrapMiddleware(Transactional::class, $container),
    new TacticianWrapMiddleware(Metrics::class, $container),
    // Handler execution
    new CommandHandlerMiddleware($extractor, $locator, $inflector),
]);
```

**Middleware Execution Order:**

```
Incoming request
    ↓
[Logging] → Logs start
    ↓
[Transactional] → Opens transaction
    ↓
[Metrics] → Starts timer
    ↓
Handler execution
    ↓
[Metrics] → Records time
    ↓
[Transactional] → Commit or Rollback
    ↓
[Logging] → Logs result
    ↓
Response
```

**Implemented Aspects:**

```
src/Application/Aspects/
├── Logging.php           # Logs handler entry/exit/errors
├── Transactional.php     # Wraps in DB transaction
├── Metrics.php           # Collects execution time metrics
└── Caching.php           # Caches query results
```

**Testing Middleware:**

Each middleware can be tested in isolation by invoking it directly:

```php
public function testLoggingAspect(FunctionalTester $i): void
{
    $container = DiHelper::container();
    $logging = $container->get(Logging::class);

    $logging(new Envelope(new Ping('test'), '1'), new PingHandler());

    $i->seeLoggerHasInfoThatContains('Start handle');
    $i->seeLoggerHasInfoThatContains('Finish handle');
}
```

**References:**

- [Middleware Pattern](https://www.php-fig.org/psr/psr-15/)
- [Tactician Command Bus](https://tactician.thephpleague.com/)
- [Pipeline Pattern](https://refactoring.guru/design-patterns/chain-of-responsibility)
