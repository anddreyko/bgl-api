# Stage 1: OptionalAuthInterceptor (PLAYS-003)

## Goal

Create interceptor that optionally extracts userId from JWT without requiring authentication.

## Tasks

### 1. Create OptionalAuthInterceptor

**File:** `src/Presentation/Api/Interceptors/OptionalAuthInterceptor.php`

Follow `AuthInterceptor.php` structure:

```php
final readonly class OptionalAuthInterceptor implements Interceptor {
    public function __construct(
        private Authenticator $authenticator,
    ) {}

    public function process(ServerRequestInterface $request): ServerRequestInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            return $request->withAttribute('auth.userId', null);
        }

        try {
            $token = substr($header, 7);
            $authPayload = $this->authenticator->verify($token);
            return $request->withAttribute('auth.userId', $authPayload->userId);
        } catch (\Throwable) {
            return $request->withAttribute('auth.userId', null);
        }
    }
}
```

### 2. Unit Tests

**File:** `tests/Unit/Presentation/Api/Interceptors/OptionalAuthInterceptorCest.php`

Follow `tests/Unit/Presentation/Api/Interceptors/AuthInterceptorCest.php` pattern:

- `testWithValidTokenSetsUserId`: mock Authenticator->verify returns payload, check attribute
- `testWithoutTokenSetsNull`: no Authorization header, userId attribute is null
- `testWithInvalidTokenSetsNull`: Authenticator throws, userId attribute is null

## Validation

```bash
composer lp:run && composer ps:run && composer test:unit
```
