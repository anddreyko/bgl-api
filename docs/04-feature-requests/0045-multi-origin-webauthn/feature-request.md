# Multi-Origin WebAuthn Support

**Beads:** bgl-xf0
**Status:** Draft
**Context:** Auth (Infrastructure)

## Problem

WebAuthn RP ID is hardcoded as a single value via `WEBAUTHN_RP_ID` env variable. The API serves multiple frontend domains (e.g. `localhost`, `anddreyko.duckdns.org`, `4record.duckdns.org`). Passkey registration/login fails when the browser's origin doesn't match the configured RP ID.

## Goal

Allow passkey registration and authentication from multiple frontend domains. Each domain gets its own RP ID. Passkeys registered on one domain are scoped to that domain (WebAuthn spec limitation).

## Scope

- Backend only (API changes)
- Frontend sends its origin; backend resolves RP ID from whitelist
- No changes to WebAuthn spec behavior (one passkey = one domain)

## Requirements

1. `WEBAUTHN_RP_ID` replaced by `WEBAUTHN_ALLOWED_ORIGINS` (comma-separated list of allowed domains)
2. Passkey endpoints receive `origin` parameter from frontend (via `Origin` or `Referer` header, or explicit param)
3. Backend extracts domain from origin, validates against whitelist, uses as RP ID
4. Reject requests from unlisted origins with clear error
5. Existing passkeys on `localhost` continue to work if `localhost` is in the whitelist

## Non-Goals

- Cross-domain passkey sharing (not possible per WebAuthn spec without Related Origin Requests)
- Related Origin Requests (requires shared parent domain, not applicable for unrelated domains)

## Affected Files

- `src/Core/Auth/PasskeyVerifier.php` -- interface methods gain `origin` parameter
- `src/Infrastructure/Auth/WebAuthnPasskeyVerifier.php` -- accept origin, resolve RP ID
- `config/common/security.php` -- parse `WEBAUTHN_ALLOWED_ORIGINS`
- `.env` -- replace `WEBAUTHN_RP_ID` with `WEBAUTHN_ALLOWED_ORIGINS`
- Passkey handlers (4 handlers) -- extract and pass origin
- Passkey API routes/actions -- extract Origin header
- Tests for all changed components
