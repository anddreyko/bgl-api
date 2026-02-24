# Documentation: User Info

> FR: 0013-user-001-user-info
> Completed: 2026-02-23

## Summary

Implemented endpoint to retrieve authenticated user information. Returns basic profile data: id, email, active status, registration date. Protected endpoint requiring valid Bearer token.

## Key Files

| File | Purpose |
|------|---------|
| `src/Application/Handlers/User/GetUser/Query.php` | Query to fetch user by ID |
| `src/Application/Handlers/User/GetUser/Handler.php` | Retrieves user from repository and returns Result |
| `src/Application/Handlers/User/GetUser/Result.php` | User data result VO |
| `tests/Unit/Application/Handlers/User/GetUser/HandlerCest.php` | Unit tests |
| `config/common/openapi/user.php` | GET /v1/user/{id} endpoint definition |

## How It Works

1. Client sends GET /v1/user/{id} with Bearer token
2. AuthInterceptor validates token and extracts userId
3. Handler receives Query with userId from path parameter
4. Handler fetches User entity from repository
5. If user not found, throws DomainException (404)
6. Handler returns Result with user data
7. SchemaResponseSerializer serializes Result to JSON

Response includes:
- User ID (UUID)
- Email address
- Active status (boolean)
- Created at timestamp

## Testing

Unit tests cover:
- Handler returns correct Result for existing user
- Handler throws exception for non-existent user
- Result contains all required fields
