# Feature Request: AUTH-003 Token Refresh

**Task:** bgl-8f2
**Date:** 2026-02-23
**Status:** Completed
**Priority:** P1

---

## 1. Feature Overview

### Description

POST /v1/auth/refresh: accepts refreshToken in body, validates by signature, verifies user active, returns new access+refresh token pair. Public endpoint (no AuthInterceptor), validates refresh token from request body.

### Endpoint

| Method | Path | Auth | Response |
|--------|------|------|----------|
| POST | /v1/auth/refresh | No | `{"code": 0, "data": {"access_token": "...", "refresh_token": "...", "expires_in": 7200}}` |

### Errors

- 401: Invalid or expired refresh token
