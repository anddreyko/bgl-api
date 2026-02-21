# League PHP Ecosystem Preference

## Date: 2025-12-29

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

BoardGameLog requires multiple infrastructure packages for API development, authentication, messaging, and future event
sourcing capabilities. The PHP ecosystem offers several package families that provide overlapping functionality,
primarily Symfony components and League PHP packages. A consistent strategy for package selection is needed to minimize
dependency conflicts, reduce learning curve, and ensure long-term maintainability.

**Current situation:**

The project already uses several League packages:

- `league/tactician` — Command Bus (MessageBus implementation)
- `league/oauth2-server` — OAuth2 authentication (planned for AUTH-001)

**Requirements:**

- Framework-agnostic packages compatible with Slim 4
- Lightweight dependencies aligned with Clean Architecture
- PSR compliance for interoperability
- Active maintenance and PHP 8.4 compatibility
- Consistent API design across packages

---

### Considered Options

#### Option 1: Symfony-First Approach

Use Symfony components as the primary source for infrastructure packages.

**Pros:**

- Comprehensive documentation and large community
- Tight integration between Symfony packages
- Battle-tested in production environments
- Many packages available for various needs

**Cons:**

- Symfony packages often bring heavy dependency chains
- Designed primarily for Symfony framework integration
- Can lead to "framework creep" in non-Symfony projects
- Some components are over-engineered for simple use cases

#### Option 2: Mixed Ecosystem (Case-by-Case)

Evaluate each package need individually without ecosystem preference.

**Pros:**

- Always choose the "best" tool for each specific job
- Maximum flexibility

**Cons:**

- Inconsistent APIs across different package families
- Higher cognitive overhead for developers
- Potential dependency conflicts between ecosystems
- No clear decision framework

#### Option 3: League PHP Ecosystem Preference

Prefer League PHP packages where suitable alternatives exist, supplementing with other ecosystems when necessary.

**Pros:**

- League packages follow PHP-FIG standards (PSR)
- Designed for framework-agnostic use
- Lightweight with minimal dependencies
- Consistent API design philosophy across packages
- Already established in the project (Tactician, OAuth2)

**Cons:**

- Smaller community than Symfony
- Fewer packages available overall
- May need to supplement with other ecosystems for specialized needs

---

### Decision

**Decision:** Adopt League PHP ecosystem preference (Option 3)

When selecting infrastructure packages, the following priority order applies:

1. **League PHP packages** — if available and meeting requirements
2. **Specialized ecosystems** — for domain-specific needs (e.g., EventSauce for event sourcing)
3. **Other PSR-compliant packages** — lightweight, framework-agnostic alternatives
4. **Symfony components** — only when no suitable alternative exists

**Reason for choice:**

1. **Consistency** — unified API patterns across infrastructure code
2. **Lightweight** — minimal dependency footprint aligns with Slim framework
3. **Framework-agnostic** — supports Clean Architecture principle of infrastructure independence
4. **Established foundation** — Tactician and OAuth2 already in use
5. **PSR compliance** — ensures interoperability and future flexibility

---

### Consequences

**Positive:**

- Predictable package selection process for the team
- Reduced dependency conflicts due to consistent ecosystem
- Lighter application footprint
- Easier onboarding as developers learn one ecosystem's patterns
- Clear decision framework eliminates analysis paralysis

**Negative/Risks:**

- Some League packages may have fewer features than Symfony equivalents (mitigated by evaluating requirements first)
- Smaller community for support (mitigated by good documentation and PSR compliance)
- May occasionally need to deviate from preference for specialized needs (acceptable and documented)

---

### Notes

**League packages currently in use or planned:**

| Package                | Purpose                     | Status                |
|------------------------|-----------------------------|-----------------------|
| `league/tactician`     | Command/Query Bus           | In use                |
| `league/oauth2-server` | OAuth2 authentication       | Planned (AUTH-001)    |
| `league/fractal`       | API response transformation | Planned (see ADR-010) |
| `league/flysystem`     | File system abstraction     | Potential future use  |

**When to deviate from League preference:**

- No League package exists for the requirement
- League package lacks critical features for the use case
- Specialized ecosystem provides significantly better fit (e.g., EventSauce for event sourcing)
- Performance requirements demand a specific solution

Deviations should be documented in relevant ADRs with justification.

**References:**

- [The League of Extraordinary Packages](https://thephpleague.com/)
- [PHP-FIG Standards](https://www.php-fig.org/)
