# Glossary

**Document Version:** 1.0

---

## Business Terms

### Play (Session)

One completed gaming session of a group of players in one board game. A session records date, time, participants, and
results. It is the central unit of accounting in the system.

**Synonyms:** Session, Game Session

**Examples:**

- A Carcassonne session on December 15 with 4 participants
- A Sunday Gloomhaven session lasting 3 hours

---

### Game (Board Game)

A specific board game from the BoardGameGeek catalog or local storage. Can be a base game or expansion.

**Attributes:**

- Name
- BGG ID — unique identifier on BoardGameGeek
- Type — base game or expansion
- Release year
- Player range (min/max)
- Play time (min/max)

---

### Player

A participant in a specific session. A player can be linked to a registered system user (User) or exist as a guest
record with a name.

**Attributes:**

- Name
- Result (winner/loser/place)
- Score (if applicable)
- First player flag

---

### Mate (Co-player)

A connection between two registered users for shared access to statistics of joint sessions. Co-players can view and
edit shared sessions.

**Connection Statuses:**

- Pending — awaiting confirmation
- Confirmed — confirmed by both parties
- Rejected — declined

---

### User

A registered BoardGameLog system user. A user can create sessions, be a player, have co-players, and synchronize with
BGG.

---

### Stats (Statistics)

Aggregated analytical data calculated from recorded sessions. Includes game tops, win percentages, play frequency, and
other metrics.

**Statistics Types:**

- Personal Stats — user's personal statistics
- Game Stats — statistics for a specific game
- Group Stats — co-player group statistics
- Period Stats — statistics for a period (month/year)

---

### BGG (BoardGameGeek)

The largest online board game database and community. BoardGameLog integrates with BGG for game search and session
synchronization.

**URL:** https://boardgamegeek.com

---

## Technical Terms

### Event Sourcing

An architectural pattern where system state is reconstructed from a sequence of events. Events are stored in Event Store
as the single source of truth.

**Note:** BoardGameLog does NOT use Event Sourcing in MVP. The project uses domain events without persistent storage —
events are processed within transactions but not stored for replay. See ADR-006 for the phased approach to event-driven
architecture.

**Event Example (domain event, not stored):**

```
PlayCreated { playId, gameId, occurredAt }
PlayerAdded { playId, playerId }
ResultRecorded { playId, playerId, isWinner, score }
```

---

### Aggregate

A cluster of domain objects that can be treated as a single unit. In DDD context, an aggregate has a root entity (
Aggregate Root) and consistency boundaries.

**Aggregate Examples in BGL:**

- Play (root) + Players
- User (root) + Mates
- Game (root) + Expansions

---

### Value Object

An immutable object defined by its attributes rather than identity. Two Value Objects with identical attributes are
considered equal.

**Examples in BGL:**

- `Email` — a valid email address
- `PlayerId` — UUID player identifier
- `Score` — non-negative integer score

---

### Entity

A domain object with unique identity that persists throughout its lifecycle. Two entities are equal if their identifiers
are equal.

**Examples in BGL:**

- `Play` — identified by `PlayId`
- `User` — identified by `UserId`
- `Game` — identified by `GameId`

---

### Repository

A data access abstraction hiding storage details. A repository works with aggregates as in-memory collections.

**Contract:**

- `add(entity)` — add entity
- `find(id)` — find by identifier
- `remove(entity)` — remove entity
- `search(filter)` — find by criteria

---

### Handler

An Application Layer class implementing one specific use case. A handler coordinates domain entities and infrastructure
services. Handlers are located in use-case folders: `Application/Handlers/{Context}/{UseCase}/Handler.php`.

**Examples:**

- `Handlers/Plays/CreatePlay/Handler.php` — creating a new session
- `Handlers/Auth/IssueToken/Handler.php` — issuing JWT token
- `Handlers/Games/SearchGames/Handler.php` — searching games via BGG

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

- `Logging` — logs handler entry/exit/errors
- `Transactional` — wraps in DB transaction
- `Caching` — caches query results

Aspects are configured in DI container as middleware, not via attributes on classes.

---

### Clean Architecture

An architectural approach with concentric layers and inward-directed dependencies. Inner layers do not depend on outer
layers.

**BGL Layers:**

1. **Core** — contracts, interfaces, base VOs
2. **Domain** — business logic, entities, rules
3. **Application** — use cases, handlers
4. **Infrastructure** — DB, external services
5. **Presentation** — API, CLI

---

### DDD (Domain-Driven Design)

A development approach based on domain modeling. Key concepts: Ubiquitous Language, Bounded Context, Aggregates.

---

### Bounded Context

An explicit boundary within which a domain model has a specific meaning. Different contexts may have different models of
the same concepts.

**BGL Contexts:**

- Auth Context
- Games Context
- Plays Context
- Stats Context
- Sync Context

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

---

## Statuses and States

### Sync Status

| Status       | Description                       |
|--------------|-----------------------------------|
| `not_synced` | Session not synchronized with BGG |
| `pending`    | Awaiting synchronization          |
| `synced`     | Successfully synchronized         |
| `failed`     | Synchronization error             |

### Mate Connection Status

| Status      | Description                         |
|-------------|-------------------------------------|
| `pending`   | Request sent, awaiting confirmation |
| `confirmed` | Connection confirmed                |
| `rejected`  | Request declined                    |
| `blocked`   | User blocked                        |

### Game Type

| Type                   | Description            |
|------------------------|------------------------|
| `base`                 | Base game              |
| `expansion`            | Expansion to base game |
| `standalone_expansion` | Standalone expansion   |

### Result Type

| Type        | Description      |
|-------------|------------------|
| `winner`    | Winner           |
| `loser`     | Loser            |
| `tie`       | Tie              |
| `coop_win`  | Cooperative win  |
| `coop_loss` | Cooperative loss |
