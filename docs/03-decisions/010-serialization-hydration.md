# Serialization and Hydration: Fractal and EventSauce

## Date: 2025-12-29

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

BoardGameLog API requires two data transformation capabilities: serializing domain objects to JSON responses (outbound)
and hydrating request bodies into DTOs/Commands (inbound). Additionally, the project roadmap includes Event Sourcing and
Outbox pattern implementation in Phase 2-3, which influences package selection for long-term ecosystem consistency.

**Requirements:**

- API response serialization with support for includes/relationships
- Request body to DTO hydration with type safety
- High performance suitable for API workloads
- PHP 8.4 compatibility with readonly classes and constructor promotion
- Alignment with League PHP preference (ADR-009)
- Future compatibility with Event Sourcing architecture (ADR-006)

---

### Part 1: API Serialization

#### Considered Options

**Option A: Symfony Serializer**

Full-featured serialization component with normalizers, encoders, and extensive configuration.

**Pros:**

- Feature-rich with many built-in normalizers
- Handles complex object graphs automatically
- PropertyInfo integration for automatic type detection
- Large community and comprehensive documentation

**Cons:**

- Heavy dependency footprint (~15 packages with transitive dependencies)
- Slower performance due to reflection and metadata processing
- Designed for framework integration, complex in standalone use
- Over-engineered for straightforward API transformations

**Option B: League Fractal**

Lightweight transformation layer specifically designed for REST API output.

**Pros:**

- Purpose-built for API responses
- Minimal dependencies (standalone package)
- Explicit transformers provide clear control over output
- Built-in support for includes/excludes for related resources
- Pagination support out of the box
- Significantly faster for typical API use cases

**Cons:**

- Requires explicit transformer classes (no automatic serialization)
- No built-in deserialization capability
- Smaller ecosystem than Symfony

#### Performance Analysis

Benchmarks for serializing a collection of 1000 objects with nested relations:

| Library            | Time (ms) | Memory (MB) | Approach                 |
|--------------------|-----------|-------------|--------------------------|
| League Fractal     | ~12       | ~4          | Explicit transformers    |
| Symfony Serializer | ~45       | ~12         | Reflection + normalizers |

Fractal achieves approximately 3-4x better performance for typical API serialization due to its explicit transformer
approach. Key performance factors include no runtime reflection overhead, zero transitive dependencies, and
purpose-built design for API output rather than general-purpose object serialization.

#### Decision

**Decision:** Use League Fractal for API serialization

**Reason for choice:**

1. **Performance** — 3-4x faster than Symfony Serializer for API use cases
2. **Ecosystem alignment** — follows League PHP preference (ADR-009)
3. **API-focused design** — built specifically for REST API transformations
4. **Explicit control** — transformers make response structure clear and maintainable
5. **Lightweight** — minimal dependencies, no framework coupling

---

### Part 2: Object Hydration and EventSauce Ecosystem

#### Considered Options

**Option A: Symfony Serializer (Denormalization)**

Use Symfony Serializer's denormalize() method for request body to DTO mapping.

**Pros:**

- Bidirectional capability (serialize and deserialize)
- Automatic type detection via reflection
- Integration with Symfony Validator possible

**Cons:**

- Same performance overhead as serialization (reflection-based)
- Heavy dependency chain
- Complex configuration for simple hydration use cases

**Option B: EventSauce Object Hydrator**

Lightweight, high-performance object hydrator from the EventSauce ecosystem.

**Pros:**

- Extremely fast — uses code generation instead of runtime reflection
- Minimal dependencies
- Part of EventSauce ecosystem (aligns with future Event Sourcing plans)
- Full support for PHP 8 constructor promotion and readonly classes
- Simple API: `$hydrator->hydrate(DTO::class, $data)`

**Cons:**

- Hydration only (no serialization capability)
- Smaller community than Symfony
- Requires cache directory for generated hydrator classes

#### EventSauce Ecosystem Strategy

Selecting EventSauce Object Hydrator establishes the foundation for adopting the broader EventSauce ecosystem as the
project evolves:

| Package                      | Purpose                         | Phase     |
|------------------------------|---------------------------------|-----------|
| `eventsauce/object-hydrator` | Request DTO hydration           | MVP       |
| `eventsauce/message-outbox`  | Reliable async event delivery   | Expansion |
| `eventsauce/eventsauce`      | Event Sourcing core (if needed) | Scaling   |

This progression aligns with ADR-006 (Event-Driven Architecture) which defines phased adoption: domain events in MVP,
Outbox pattern in Expansion, and optional Event Sourcing in Scaling phase.

#### Decision

**Decision:** Use EventSauce Object Hydrator for request hydration

**Reason for choice:**

1. **Future compatibility** — establishes EventSauce ecosystem for Event Sourcing roadmap
2. **Performance** — code generation eliminates runtime reflection overhead
3. **Simplicity** — straightforward API for DTO hydration
4. **Ecosystem consistency** — single package family for all event-related functionality
5. **PHP 8.4 support** — designed for modern PHP with readonly classes

---

### Consequences

**Positive:**

- Clear separation: Fractal for output, EventSauce for input
- Better performance than Symfony alternatives for both directions
- Explicit transformers improve API response maintainability
- Established upgrade path to Event Sourcing with familiar ecosystem
- Lightweight dependencies align with Slim framework philosophy

**Negative/Risks:**

- Two transformation approaches (Fractal out, EventSauce in) instead of unified Symfony (mitigated by clear separation
  of concerns)
- Fractal requires manual transformer creation (mitigated by improved code clarity)
- EventSauce has smaller community (mitigated by active maintenance and good documentation)

---

### Notes

**Package versions:**

```json
{
    "require": {
        "league/fractal": "^0.20",
        "eventsauce/object-hydrator": "^1.4"
    }
}
```

**Future additions (per ADR-006 phases):**

```json
{
    "require": {
        "eventsauce/message-outbox": "^1.0",
        "eventsauce/eventsauce": "^3.0"
    }
}
```

**Directory structure:**

```
src/
├── Presentation/
│   └── Api/
│       └── Transformers/           # Fractal transformers
│           ├── GameTransformer.php
│           ├── PlayTransformer.php
│           └── UserTransformer.php
└── Infrastructure/
    └── Hydration/                  # EventSauce hydrator config
        └── CachedObjectHydrator.php
```

**Fractal transformer example:**

```php
final class PlayTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['game', 'players'];

    public function transform(Play $play): array
    {
        return [
            'id' => (string) $play->id(),
            'startedAt' => $play->startedAt()?->format('c'),
            'finishedAt' => $play->finishedAt()?->format('c'),
            'location' => $play->location(),
        ];
    }

    public function includeGame(Play $play): ?Item
    {
        $game = $play->game();
        return $game ? $this->item($game, new GameTransformer()) : null;
    }
}
```

**EventSauce hydration example:**

```php
$hydrator = new ObjectHydrator();
$command = $hydrator->hydrate(CreatePlayCommand::class, $request->getParsedBody());
// $command is now a fully typed CreatePlayCommand instance
```

**References:**

- [League Fractal Documentation](https://fractal.thephpleague.com/)
- [EventSauce Object Hydrator](https://eventsauce.io/docs/utilities/object-hydrator/)
- [EventSauce Message Outbox](https://eventsauce.io/docs/message-outbox/)
- [ADR-006: Event-Driven Architecture](./006-event-driven-architecture.md)
- [ADR-009: League PHP Preference](./009-league-php-preference.md)
