# Documentation: User Registration with Email Confirmation

> FR: 0018-auth-001-registration
> Completed: 2026-02-23

## Summary

Implemented user registration via email and password with email confirmation flow. POST /v1/auth/sign-up creates inactive user and confirmation token. GET /v1/auth/confirm/{token} activates account. MVP logs confirmation URL instead of sending email.

## Key Files

| File | Purpose |
|------|---------|
| `src/Domain/Auth/Entities/User.php` | Modified to add passwordHash field and register/confirm methods |
| `src/Domain/Auth/Entities/EmailConfirmationToken.php` | Confirmation token entity with expiry (24h TTL) |
| `src/Domain/Auth/Entities/EmailConfirmationTokens.php` | Token repository interface |
| `src/Domain/Auth/Entities/Users.php` | Modified to add findByEmail method |
| `src/Domain/Auth/Exceptions/UserAlreadyExistsException.php` | Exception for duplicate email |
| `src/Domain/Auth/Exceptions/InvalidConfirmationTokenException.php` | Exception for token not found |
| `src/Domain/Auth/Exceptions/ExpiredConfirmationTokenException.php` | Exception for expired token |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/UserMapping.php` | Updated with passwordHash mapping |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/EmailConfirmationTokenMapping.php` | ORM mapping for token entity |
| `src/Infrastructure/Persistence/Doctrine/Users.php` | Updated with findByEmail implementation |
| `src/Infrastructure/Persistence/Doctrine/EmailConfirmationTokens.php` | Doctrine repository for tokens |
| `src/Application/Handlers/Auth/Register/Command.php` | Registration command |
| `src/Application/Handlers/Auth/Register/Handler.php` | Registration handler with uniqueness check |
| `src/Application/Handlers/Auth/ConfirmEmail/Command.php` | Confirmation command |
| `src/Application/Handlers/Auth/ConfirmEmail/Handler.php` | Confirmation handler with expiry check |
| `tests/Unit/Application/Handlers/Auth/Register/HandlerCest.php` | Registration tests |
| `config/common/openapi/auth.php` | POST /v1/auth/sign-up and GET /v1/auth/confirm/{token} endpoints |

## How It Works

Registration flow:
1. Client sends POST /v1/auth/sign-up with email and password
2. Handler validates email uniqueness (409 if duplicate)
3. Handler hashes password using PasswordHasher
4. Handler creates User entity with Inactive status
5. Handler generates UUID confirmation token with 24h expiry
6. Handler persists User and EmailConfirmationToken
7. Handler returns success message
8. MVP: Confirmation URL logged (future: send email)

Confirmation flow:
1. Client sends GET /v1/auth/confirm/{token}
2. Handler finds token in repository (400 if not found)
3. Handler checks token expiration (409 if expired)
4. Handler loads User and calls confirm() method
5. confirm() sets User status to Active
6. Handler deletes used token
7. Handler returns success message

Database changes:
- auth_user table: added password_hash column
- auth_email_confirmation_token table: created for tokens

## Testing

Tests cover:
- Registration with valid data
- Duplicate email rejection
- Email confirmation with valid token
- Invalid token rejection
- Expired token rejection
- Token entity expiry logic
