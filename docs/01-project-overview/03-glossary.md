# Glossary

**Document Version:** 2.0

---

## Business Terms

### Play

One board game play. Records date, time, participants, and results. The central unit of accounting in the system.

**Canonical name:** Play (not "Session").

**Examples:**

- A Carcassonne play on December 15 with 4 participants
- A Sunday Gloomhaven play lasting 3 hours

---

### Game (Board Game)

A specific board game from the BoardGameGeek catalog. Can be a base game, expansion, or standalone expansion. Games are
imported from BGG, not created manually.

**Attributes:**

- Name (primary + alternative names)
- BGG ID -- unique identifier on BoardGameGeek
- Type -- base game, expansion, or standalone expansion
- Release year
- Player range (min/max)
- Play time (min/max)
- Family -- informational grouping from BGG (community-maintained, may be inaccurate)

---

### Player

A participant in a specific play. Always references a Mate from the owner's directory.

**Attributes:**

- Mate reference (required)
- Team tag (optional, same value = same team)
- Score (optional, non-negative)
- Player number (optional, 1 = first player)
- Color/faction (optional)
- Winner flag (optional)

---

### Mate (Co-player)

An entry in a user's personal directory of co-players. Not a connection between users -- a personal record of someone
you play with.

A Mate can exist without any system linkage (just a name), or be linked to a registered User and/or a BGG account.

**System Mates (global):**

- **Anonymous** -- unknown/random player
- **Automa** -- NPC / solo mode opponent

**Belongs to:** Plays context.

---

### User

A registered BoardGameLog system user. Manages profile, creates plays, owns a mate directory and location directory.

**Belongs to:** Profile context.

---

### Location

An entry in a user's personal directory of places where plays happen. Allows tracking where games were played.

**Attributes:**

- Name (required)
- Icon/photo (future)

**Belongs to:** Plays context.

---

### Stats (Statistics)

Aggregated analytical data calculated from recorded plays. Includes game tops, win percentages, play frequency, and
other metrics. A play's `includeInStats` flag determines whether it is counted.

**Statistics Types:**

- Personal Stats -- user's personal statistics
- Game Stats -- statistics for a specific game
- Group Stats -- co-player group statistics
- Period Stats -- statistics for a period (month/year)

---

### BGG (BoardGameGeek)

The largest online board game database and community. BoardGameLog integrates with BGG for game search and play
synchronization.

**URL:** https://boardgamegeek.com

---

## Technical Terms

### Event Sourcing

An architectural pattern where system state is reconstructed from a sequence of events. Events are stored in Event Store
as the single source of truth.

**Note:** BoardGameLog does NOT use Event Sourcing in MVP. The project uses domain events without persistent storage --
events are processed within transactions but not stored for replay. See ADR-006 for the phased approach to event-driven
architecture.

**Event Example (domain event, not stored):**

```
PlayCreated { playId, userId, startedAt }        -- lifecycle: Current
PlayFinalized { playId, finishedAt? }             -- lifecycle: Finished
PlayerAdded { playId, mateId }
PlayVisibilityChanged { playId, old, new }
PlayDeleted { playId }                            -- lifecycle: Deleted
PlayRestored { playId }                           -- lifecycle: Finished
```

---

### Aggregate

A cluster of domain objects that can be treated as a single unit. In DDD context, an aggregate has a root entity (
Aggregate Root) and consistency boundaries.

Aggregate roots implement `Core\Aggregate\AggregateRoot` interface with domain event support via `Core\Aggregate\Emits`
trait (composition, no inheritance).

**Aggregate Roots in BGL:**

- **Profile context:** User (with Passkey VO)
- **Plays context:** Play (with Player children), Mate, Location
- **Games context:** Game

---

### Value Object

An immutable object defined by its attributes rather than identity. Two Value Objects with identical attributes are
considered equal.

**Examples in BGL:**

- `Email` -- a valid email address
- `Uuid` -- unique identifier
- `Password` -- validated password input (min 8 chars)

---

### Entity

A domain object with unique identity that persists throughout its lifecycle. Two entities are equal if their identifiers
are equal.

**Examples in BGL:**

- `User` -- aggregate root, identified by Uuid (Profile context)
- `Play` -- aggregate root, identified by Uuid (Plays context)
- `Player` -- child entity of Play, identified by Uuid (Plays context)
- `Mate` -- aggregate root, identified by Uuid (Plays context)
- `Location` -- aggregate root, identified by Uuid (Plays context)
- `Game` -- aggregate root, identified by Uuid (Games context)

---

### Repository

A data access abstraction hiding storage details. A repository works with aggregates as in-memory collections.

**Contract:**

- `add(entity)` -- add entity
- `find(id)` -- find by identifier
- `remove(entity)` -- remove entity
- `search(filter)` -- find by criteria

---

### Handler

An Application Layer class implementing one specific use case. A handler coordinates domain entities and infrastructure
services. Handlers are located in use-case folders: `Application/Handlers/{Context}/{UseCase}/Handler.php`.

**Examples:**

- `Handlers/Plays/CreatePlay/Handler.php` -- creating a new play
- `Handlers/Auth/IssueToken/Handler.php` -- issuing JWT token
- `Handlers/Games/SearchGames/Handler.php` -- searching games via BGG

---

### Command

A DTO representing an intent to change system state. Commands are processed by Handlers and may produce events.

**Structure:**

- Data only (no logic)
- Immutable
- Serializable

---

### Query

A DTO for requesting data without changing system state. In CQS/CQRS architecture, Queries are separated from Commands.

---

### Event

A fact of a change that occurred in the system. Events are named in past tense (`PlayCreated`, `UserRegistered`).

---

### Message Bus

An infrastructure component for routing Commands, Queries, and Events to corresponding Handlers. Implements the Mediator
pattern.

---

### Aspect

Cross-cutting functionality applied through middleware pipeline. Examples: logging, transactions, caching.

**Aspects in BGL:**

- `Logging` -- logs handler entry/exit/errors
- `Transactional` -- wraps in DB transaction
- `Caching` -- caches query results

Aspects are configured in DI container as middleware, not via attributes on classes.

---

### Clean Architecture

An architectural approach with concentric layers and inward-directed dependencies. Inner layers do not depend on outer
layers.

**BGL Layers:**

1. **Core** -- contracts, interfaces, base VOs
2. **Domain** -- business logic, entities, rules
3. **Application** -- use cases, handlers
4. **Infrastructure** -- DB, external services
5. **Presentation** -- API, CLI

---

### DDD (Domain-Driven Design)

A development approach based on domain modeling. Key concepts: Ubiquitous Language, Bounded Context, Aggregates.

---

### Bounded Context

An explicit boundary within which a domain model has a specific meaning. Different contexts may have different models of
the same concepts.

**BGL Bounded Contexts:**

- **Profile** -- user identity, profile, settings
- **Plays** -- play logging, players, mates, locations
- **Games** -- game catalog (BGG import)
- **Stats** -- analytics and reporting
- **Access** -- auth methods, device session management (Phase 4)

**Not bounded contexts (infrastructure):**

- **Auth** -- authentication/authorization mechanics (Core contracts + Infrastructure)
- **Sync** -- external integration (Core ports + Infrastructure adapters)

---

### CQRS (Command Query Responsibility Segregation)

A pattern for separating read and write models. Allows optimizing each model independently.

---

### Materialized View

A precomputed query result stored as a table. Used for optimizing complex analytical queries.

**Usage in BGL:**

- Top games for period
- Win leaderboards
- Aggregated statistics

---

## Acronyms

| Acronym | Full Form                                | Description                                  |
|---------|------------------------------------------|----------------------------------------------|
| BGL     | BoardGameLog                             | Project API name                             |
| BGG     | BoardGameGeek                            | External board game database service         |
| API     | Application Programming Interface        | Programming interface                        |
| JWT     | JSON Web Token                           | Authorization token format                   |
| DDD     | Domain-Driven Design                     | Domain-driven development                    |
| CQS     | Command Query Separation                 | Command and query separation                 |
| CQRS    | Command Query Responsibility Segregation | Command and query responsibility segregation |
| AOP     | Aspect-Oriented Programming              | Aspect-oriented programming                  |
| VO      | Value Object                             | Value object                                 |
| DTO     | Data Transfer Object                     | Data transfer object                         |
| MVP     | Minimum Viable Product                   | Minimum viable product                       |
| SLA     | Service Level Agreement                  | Service level agreement                      |
| CDC     | Change Data Capture                      | Data change capture                          |
| DAU     | Daily Active Users                       | Daily active users                           |
| MAU     | Monthly Active Users                     | Monthly active users                         |
| AR      | Aggregate Root                           | Root entity of an aggregate                  |

---

## Statuses and States

### User Status

| Status     | Description                              |
|------------|------------------------------------------|
| `inactive` | Newly registered, awaiting confirmation  |
| `active`   | Confirmed, active account                |
| `deleted`  | Soft-deleted account                     |

### Play Lifecycle (PlayLifecycle)

| Status     | Description                                              |
|------------|----------------------------------------------------------|
| `current`  | Game in progress right now                               |
| `finished` | Game completed (finishedAt optional, user may not know)  |
| `deleted`  | Soft-deleted, hidden everywhere, excluded from stats     |

Transitions: create -> Current, finalize -> Finished, delete -> Deleted, restore -> Finished.
`Current` is assigned only at creation and never restored. See ADR-016.

### Sync Status

| Status       | Description                     |
|--------------|---------------------------------|
| `not_synced` | Play not synchronized with BGG  |
| `pending`    | Awaiting synchronization        |
| `synced`     | Successfully synchronized       |
| `failed`     | Synchronization error           |

### Visibility

| Level           | Description                                        |
|-----------------|----------------------------------------------------|
| `private`       | Only the author                                    |
| `participants`  | Author + users linked to mates in this play        |
| `link`          | Anyone with a direct link                          |
| `authenticated` | All authenticated users (default)                  |
| `public`        | Everyone, including unauthenticated                |

### Game Type

| Type                   | Description            |
|------------------------|------------------------|
| `base`                 | Base game              |
| `expansion`            | Expansion to base game |
| `standalone_expansion` | Standalone expansion   |
