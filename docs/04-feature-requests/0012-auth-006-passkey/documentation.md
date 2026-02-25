# AUTH-006: Passkey (WebAuthn) Authentication

## Summary

FIDO2/WebAuthn passkey authentication as an alternative passwordless auth method. After passkey verification, the server issues the same JWT + refresh token pair as credential-based login.

## Architecture

### TokenIssuer extraction (SRP)

`JwtAuthenticator` previously mixed credential verification and token issuance. Extracted `TokenIssuer` interface with `JwtTokenIssuer` implementation to allow both credential-based and passkey authentication to share the same token issuance logic.

- `Core/Auth/TokenIssuer` -- interface
- `Infrastructure/Auth/JwtTokenIssuer` -- implementation
- ADR: `docs/03-decisions/013-passkey-authentication.md`

### Domain model

Entities in `Domain/Profile/Entities/`:

- `Passkey` -- stores credential ID, credential data, counter, optional label
- `PasskeyChallenge` -- temporary challenge storage with expiration, optional userId for registration

Repository interfaces: `Passkeys`, `PasskeyChallenges`

### Persistence

- Tables: `auth_passkey`, `auth_passkey_challenge`
- Doctrine mappings: `Mapping/Auth/PasskeyMapping`, `Mapping/Auth/PasskeyChallengeMapping`
- Migration: `Version20260225114838`

### PasskeyVerifier

Core interface (`Core/Auth/PasskeyVerifier`) with 4 methods:
- `registerOptions()` -- generate WebAuthn registration options JSON
- `register()` -- verify registration response, return credential data
- `loginOptions()` -- generate WebAuthn login options JSON
- `login()` -- verify login response, return updated counter

Infrastructure adapter: `WebAuthnPasskeyVerifier` using `lbuchs/webauthn`.

`registerOptions()` and `loginOptions()` return `PasskeyOptions` (optionsJson + challenge), because the library generates challenges internally.

### API endpoints

| Endpoint | Method | Auth | Handler |
|---|---|---|---|
| `/v1/auth/passkey/register` | POST | Required | RegisterPasskeyOptions |
| `/v1/auth/passkey/register/verify` | POST | Required | RegisterPasskeyVerify |
| `/v1/auth/passkey/sign-in` | POST | Public | PasskeySignInOptions |
| `/v1/auth/passkey/sign-in/verify` | POST | Public | PasskeySignInVerify |

## Environment variables

- `WEBAUTHN_RP_ID` -- Relying Party ID (domain)
- `WEBAUTHN_RP_NAME` -- Relying Party display name

## Dependencies added

- `lbuchs/webauthn` -- lightweight FIDO2/WebAuthn library (zero dependencies)

## Files created

- `src/Core/Auth/TokenIssuer.php`
- `src/Core/Auth/PasskeyVerifier.php`
- `src/Core/Auth/CredentialResult.php`
- `src/Core/Auth/PasskeyOptions.php`
- `src/Core/Auth/TokenPair.php`
- `src/Domain/Profile/Entities/Passkey.php`
- `src/Domain/Profile/Entities/PasskeyChallenge.php`
- `src/Domain/Profile/Entities/Passkeys.php`
- `src/Domain/Profile/Entities/PasskeyChallenges.php`
- `src/Infrastructure/Auth/JwtTokenIssuer.php`
- `src/Infrastructure/Auth/WebAuthnPasskeyVerifier.php`
- `src/Infrastructure/Persistence/Doctrine/Passkeys.php`
- `src/Infrastructure/Persistence/Doctrine/PasskeyChallenges.php`
- `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/PasskeyMapping.php`
- `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/PasskeyChallengeMapping.php`
- `src/Infrastructure/Persistence/InMemory/Passkeys.php`
- `src/Infrastructure/Persistence/InMemory/PasskeyChallenges.php`
- `src/Infrastructure/Database/Migrations/Version20260225114838.php`
- `src/Application/Handlers/Auth/RegisterPasskeyOptions/` (Command, Handler, Result)
- `src/Application/Handlers/Auth/RegisterPasskeyVerify/` (Command, Handler)
- `src/Application/Handlers/Auth/PasskeySignInOptions/` (Command, Handler, Result)
- `src/Application/Handlers/Auth/PasskeySignInVerify/` (Command, Handler, Result)
- `docs/03-decisions/013-passkey-authentication.md`
- `tests/Unit/Domain/Profile/Entities/PasskeyCest.php` (4 tests)
- `tests/Unit/Domain/Profile/Entities/PasskeyChallengeCest.php` (5 tests)
- `tests/Unit/Infrastructure/Auth/JwtTokenIssuerCest.php` (2 tests)
- `tests/Functional/Auth/RegisterPasskeyOptionsCest.php` (3 tests)
- `tests/Functional/Auth/PasskeySignInCest.php` (6 tests)

## Files modified

- `src/Infrastructure/Auth/JwtAuthenticator.php` -- inject TokenIssuer instead of TokenConfig
- `config/common/security.php` -- PasskeyVerifier + TokenIssuer registration
- `config/common/persistence.php` -- Passkeys + PasskeyChallenges repositories
- `config/common/doctrine.php` -- PasskeyMapping + PasskeyChallengeMapping
- `config/common/openapi/auth.php` -- 4 new passkey endpoints
- `tests/Unit/Infrastructure/Auth/JwtAuthenticatorCest.php` -- updated for new constructor
