# ADR-013: Passkey (WebAuthn) Authentication

## Date: 2026-02-25

## Authors: Team

## Status: Accepted

---

### Context

The application currently supports only password-based authentication via `Authenticator.login()`. Users increasingly
expect passwordless login options. Passkey (FIDO2/WebAuthn) provides a phishing-resistant, hardware-backed alternative.

The current `JwtAuthenticator` mixes two responsibilities: credential verification and token issuance. Adding passkey
authentication would require duplicating token issuance logic. This needs to be separated first.

### Considered Options

#### Option 1: Add passkey methods to existing Authenticator

Extend the `Authenticator` interface with passkey-specific methods (`loginByPasskey`, `registerPasskey`, etc.).

**Pros:**

- Minimal new abstractions
- Single entry point for all auth

**Cons:**

- Violates SRP: one class handles passwords, tokens, and WebAuthn
- Token issuance logic duplicated for each auth method
- Authenticator grows unbounded with each new auth method

#### Option 2: Extract TokenIssuer, separate PasskeyVerifier

Extract token issuance into `TokenIssuer`. Create a separate `PasskeyVerifier` for WebAuthn cryptographic operations.
Handlers orchestrate the flow.

**Pros:**

- Each component has single responsibility
- Token issuance reusable for any auth method
- PasskeyVerifier is pure WebAuthn, no JWT knowledge
- Easy to add future auth methods (OAuth, magic link) without touching existing code

**Cons:**

- More interfaces and classes
- Handlers become orchestrators instead of one-liners

### Decision

**Decision:** Option 2 -- Extract TokenIssuer + separate PasskeyVerifier.

**Reason for choice:** Clean separation of concerns. `TokenIssuer` handles JWT issuance for any auth method.
`PasskeyVerifier` handles only WebAuthn cryptography. Application handlers orchestrate the flow. Adding new auth methods
in the future requires only a new verifier + handler, without modifying existing code.

### Consequences

**Positive:**

- `JwtAuthenticator` simplified: delegates token issuance to `TokenIssuer`
- Passkey flow is isolated: `PasskeyVerifier` knows nothing about JWT
- Future auth methods (OAuth, magic link) reuse `TokenIssuer`
- Challenge storage in database provides audit trail and TTL enforcement

**Negative/Risks:**

- WebAuthn library (`lbuchs/webauthn`) adds a dependency
- Challenge table requires periodic cleanup of expired entries
- Four new API endpoints increase API surface

### Notes

Architecture:

```
Core/Auth/TokenIssuer         -- interface for token pair issuance
Core/Auth/PasskeyVerifier     -- interface for WebAuthn crypto operations
Infrastructure/Auth/JwtTokenIssuer       -- JWT implementation of TokenIssuer
Infrastructure/Auth/WebAuthnPasskeyVerifier -- lbuchs/webauthn adapter
Core/Auth/PasskeyOptions                  -- DTO for options JSON + challenge
```

Passkey is a Value Object within User aggregate (`Domain/Profile/ValueObjects/`).
PasskeyChallenge is an ephemeral infrastructure object (`Infrastructure/Auth/`), not a domain entity.
Doctrine mappings in `Infrastructure/Persistence/Doctrine/Mapping/Auth/` (auth convention).
Database tables: `auth_passkey`, `auth_passkey_challenge`.
