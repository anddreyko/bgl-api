# Feature Request: AUTH-002 Authentication (Login)

**Task:** bgl-mrx
**Date:** 2026-02-23
**Status:** Completed
**Priority:** P0

---

## 1. Feature Overview

### Description

Email+password login returning JWT access/refresh token pair. Refactor existing LoginByCredentials stub handler into real implementation. User must have Active status.

### Endpoint

| Method | Path | Auth | Response |
|--------|------|------|----------|
| POST | /v1/auth/sign-in | No | `{"code": 0, "data": {"access_token": "...", "refresh_token": "...", "expires_in": 7200}}` |

### Errors

- 401: Wrong credentials (DomainException)
- 403: Unconfirmed email (UserNotConfirmedException)
