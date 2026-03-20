# Master Checklist: Multi-Origin WebAuthn

**Beads:** bgl-xf0

## Stage 1: Core Contract Changes

- [ ] Add `origin` parameter to `PasskeyVerifier::registerOptions()`
- [ ] Add `origin` parameter to `PasskeyVerifier::register()`
- [ ] Add `origin` parameter to `PasskeyVerifier::loginOptions()`
- [ ] Add `origin` parameter to `PasskeyVerifier::login()`
- [ ] Psalm passes

## Stage 2: Infrastructure -- WebAuthnPasskeyVerifier [P]

- [ ] Replace single `$rpId` with `AllowedOrigins` value object (list of allowed domains)
- [ ] `resolveRpId(string $origin): string` -- extract domain from origin, validate against whitelist
- [ ] `createWebAuthn()` accepts RP ID parameter instead of using class field
- [ ] All 4 public methods use `resolveRpId()` before creating WebAuthn instance
- [ ] Throw `AuthenticationException` for unlisted origins
- [ ] Unit tests for `AllowedOrigins` value object
- [ ] Unit tests for origin resolution (valid, invalid, missing from whitelist)

## Stage 3: DI Configuration [P]

- [ ] `.env`: replace `WEBAUTHN_RP_ID=localhost` with `WEBAUTHN_ALLOWED_ORIGINS=localhost`
- [ ] `config/common/security.php`: parse comma-separated `WEBAUTHN_ALLOWED_ORIGINS`
- [ ] Construct `WebAuthnPasskeyVerifier` with `AllowedOrigins` instead of single string
- [ ] Backward compat: if `WEBAUTHN_RP_ID` is set (old config), use it as single-item list

## Stage 4: Handlers & Presentation -- Pass Origin

- [ ] Extract `Origin` header in passkey API actions (or middleware)
- [ ] Pass origin to all 4 passkey handlers via Command/Query
- [ ] Handlers pass origin to `PasskeyVerifier` methods
- [ ] Functional tests: passkey register + login with valid origin
- [ ] Functional tests: passkey register with unlisted origin returns error

## Stage 5: Cleanup & Documentation

- [ ] Remove `WEBAUTHN_RP_ID` from `.env.example` (if exists)
- [ ] Update troubleshooting docs if needed
- [ ] Run `make scan` -- all green

## Validation Criteria

- Passkey registration works from `localhost`
- Passkey registration works from `anddreyko.duckdns.org`
- Passkey registration from unlisted domain returns clear error
- Existing tests pass
- `make scan` green
