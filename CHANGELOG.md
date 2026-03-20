# 2.13.3 (2026-03-20)

### New features

- email verification flow with OTP code and magic link
- restructured auth API namespaces (password, passkey, email)
- passkey (WebAuthn) authentication support
- unauthenticated access to public play listings
- entity references returned as {id, name} objects
- read-only status field in API responses and list filter
- user profile update endpoint (PATCH /v1/user/{id})
- token pair returned from email confirmation
- user name and email included in access token
- notes and location support for play sessions
- user lookup by UUID or name
- public profile browsing with author filter
- strict request schema validation
- visibility-based access control for sessions
- paginated session list
- play session update endpoint
- player entity with cascade persist
- game detail endpoint
- optimistic locking for user entity
- OpenAPI spec with responses, operationId, tags, security schemes
- mates CRUD with sort/order params
- games search with BoardGameGeek integration and local fallback
- games and mates persistence layer
- request body trimming middleware
- configurable request deserialization
- listing system with contains filter and count
- OpenAPI spec export CLI command
- user name field
- server-side session revocation via token versioning
- configurable token TTL
- session close endpoint
- sign-out, user info, and play session endpoints
- token refresh
- JWT-based login with token pair
- user registration with email confirmation
- input and request validation
- production Docker image with healthchecks

### Fixes

- schema assets filter blocking database migrations
- WebAuthn passkey registration and sign-in flow
- base64url encoding for WebAuthn challenges
- OpenAPI schema for entity references and optional auth
- Docker volume persistence across rebuilds
- graceful shutdown and health checks
- application logging to stderr
- various API contract and schema alignment fixes

### Performance

- batch mate lookups instead of N+1 queries

# 2.0.0 (2025-11-07)

Initialize new version
