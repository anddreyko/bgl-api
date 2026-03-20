# Feature Request: AUTH-001 User Registration with Email Confirmation

**Task:** bgl-zck
**Date:** 2026-02-23
**Status:** Completed
**Priority:** P0

---

## 1. Feature Overview

### Description

User registration via email+password with email confirmation flow. POST /v1/auth/password/sign-up creates inactive user and confirmation token. GET /v1/auth/email/verify activates account.

### Business Value

- Core authentication foundation for the platform
- Email confirmation prevents spam registrations

---

## 2. Technical Architecture

### Approach

- Extend User entity: add passwordHash field, remove readonly, add register() factory and confirm() method
- New EmailConfirmationToken entity with UUID token, userId, expiresAt (24h TTL)
- Register handler: validate uniqueness, hash password, create inactive user + token
- ConfirmEmail handler: find token, check expiry, activate user, delete token
- MVP: log confirmation URL instead of sending email

### Endpoints

| Method | Path | Auth | Response |
|--------|------|------|----------|
| POST | /v1/auth/password/sign-up | No | `{"code": 0, "data": "Confirm the specified email"}` |
| GET | /v1/auth/email/verify | No | `{"code": 0, "data": "Specified email is confirmed"}` |

### Errors

- 409: UserAlreadyExistsException (duplicate email)
- 400: InvalidConfirmationTokenException (token not found)
- 409: ExpiredConfirmationTokenException (token expired)
