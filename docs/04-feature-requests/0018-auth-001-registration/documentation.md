# Documentation: User Registration with Email Confirmation

> FR: 0018-auth-001-registration
> Completed: 2026-02-23

## Summary

Implemented user registration via email and password with email confirmation flow. POST /v1/auth/password/sign-up creates inactive user and confirmation token. GET /v1/auth/email/verify activates account. MVP logs confirmation URL instead of sending email.

## Key Files

| File | Purpose |
|------|---------|
| `src/Domain/Profile/Entities/User.php` | Modified to add passwordHash field and register/confirm methods |
| `src/Domain/Profile/Entities/Users.php` | Modified to add findByEmail method |
| `src/Domain/Profile/Exceptions/UserAlreadyExistsException.php` | Exception for duplicate email |
| `src/Core/Auth/Confirmer.php` | Facade interface for email confirmation (request/confirm) |
| `src/Core/Auth/InvalidConfirmationTokenException.php` | Exception for token not found |
| `src/Core/Auth/ExpiredConfirmationTokenException.php` | Exception for expired token |
| `src/Infrastructure/Auth/DoctrineConfirmer.php` | Confirmer implementation using Doctrine |
| `src/Infrastructure/Auth/EmailConfirmationToken.php` | Confirmation token entity (infrastructure detail) |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/UserMapping.php` | Updated with passwordHash mapping |
| `src/Infrastructure/Persistence/Doctrine/Mapping/Auth/EmailConfirmationTokenMapping.php` | ORM mapping for token entity |
| `src/Infrastructure/Persistence/Doctrine/Users.php` | Updated with findByEmail implementation |
| `src/Application/Handlers/Auth/Register/Command.php` | Registration command |
| `src/Application/Handlers/Auth/Register/Handler.php` | Registration handler with uniqueness check |
| `src/Application/Handlers/Auth/ConfirmEmail/Command.php` | Confirmation command |
| `src/Application/Handlers/Auth/ConfirmEmail/Handler.php` | Confirmation handler via Confirmer service |
| `tests/Unit/Application/Handlers/Auth/Register/HandlerCest.php` | Registration tests |
| `config/common/openapi/auth.php` | POST /v1/auth/password/sign-up and GET /v1/auth/email/verify endpoints |

## How It Works

Registration flow:
1. Client sends POST /v1/auth/password/sign-up with email and password
2. Handler validates email uniqueness (409 if duplicate)
3. Handler hashes password using PasswordHasher
4. Handler creates User entity with Inactive status
5. Handler calls Confirmer::request() to generate confirmation token
6. Handler returns success message
7. MVP: Confirmation URL logged (future: send email)

Confirmation flow:
1. Client sends GET /v1/auth/email/verify
2. Handler calls Confirmer::confirm() which validates and returns userId
3. Handler loads User and calls confirm() method
4. confirm() sets User status to Active
5. Handler returns success message

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
