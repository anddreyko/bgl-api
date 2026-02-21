# ADR-012: Custom PHP Mapping Driver for Doctrine ORM

## Date: 2026-02-21

## Authors: Team

## Status: Accepted

---

### Context

The project uses Doctrine ORM for persistence. Entity metadata was configured via XML mapping files
(`config/common/doctrine/*.xml`), keeping domain entities free from ORM annotations.

Two problems with XML mapping:

1. **Symfony deprecation path** -- Symfony considers XML mapping legacy and is moving toward PHP attributes as the
   default. Long-term XML support is uncertain.
2. **Developer experience** -- XML files lack IDE autocompletion, refactoring support, and static analysis. Renaming a
   field in an entity does not propagate to the XML file.

The core requirement remains: **domain entities must have zero coupling to the persistence layer** (no Doctrine
imports, no attributes, no annotations).

### Considered Options

#### Option 1: PHP Attributes (Doctrine default)

Map entities using `#[ORM\Entity]`, `#[ORM\Column]` attributes directly on entity classes.

**Pros:**

- Official Doctrine recommendation
- Full IDE support
- Static analysis friendly

**Cons:**

- Violates architectural boundary: domain entities depend on Doctrine
- Cannot swap persistence layer without modifying domain code

#### Option 2: Keep XML Mapping

Continue using XML driver with `.xml` files in `config/common/doctrine/`.

**Pros:**

- Already working
- Entities stay clean

**Cons:**

- On Symfony deprecation path
- No IDE autocompletion or refactoring support
- No static analysis of mapping configuration
- XML is harder to read and maintain than PHP

#### Option 3: Custom PHP MappingDriver

Implement `Doctrine\Persistence\Mapping\Driver\MappingDriver` with separate PHP mapping classes in
the Infrastructure layer.

**Pros:**

- Entities stay completely clean (zero Doctrine imports)
- Full IDE support (autocompletion, refactoring, go-to-definition)
- Psalm/PHPStan can analyze mapping code
- Uses stable Doctrine API (`MappingDriver` interface, `ClassMetadata` API)
- Not affected by XML/YAML deprecation
- Mapping lives in correct architectural layer (Infrastructure)

**Cons:**

- Custom code (~50 lines for driver + interface)
- Not a standard Doctrine driver (minor learning curve)
- Each new entity requires a mapping class (vs. attributes which are co-located)

### Decision

**Decision:** Option 3 -- Custom PHP MappingDriver.

**Reason for choice:** It is the only option that satisfies both requirements simultaneously: domain entities remain
persistence-agnostic AND mapping uses modern PHP with full tooling support. The implementation cost is minimal (~50
lines of infrastructure code).

### Consequences

**Positive:**

- Domain entities have zero coupling to Doctrine ORM
- Mapping configuration is type-checked by Psalm
- IDE refactoring propagates to mapping code
- Future persistence layer changes only affect Infrastructure layer
- No dependency on deprecated mapping formats

**Negative/Risks:**

- Each new entity requires creating a mapping class and registering it in `config/common/doctrine.php`
- Developers unfamiliar with `ClassMetadata` API need to reference Doctrine documentation

### Notes

Implementation structure:

```
src/Infrastructure/Persistence/Doctrine/Mapping/
  EntityMapping.php          -- interface for mapping classes
  PhpMappingDriver.php       -- custom MappingDriver implementation
  Auth/
    UserMapping.php          -- User entity mapping
```

Configuration in `config/common/doctrine.php`:

```php
'mapping' => new PhpMappingDriver([
    new UserMapping(),
]),
```

Adding a new entity mapping:

1. Create `{Context}Mapping.php` implementing `EntityMapping`
2. Register in `config/common/doctrine.php` array
3. Register in `config/test/doctrine.php` MappingDriverChain
