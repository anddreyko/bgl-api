# Documentation: Password Hashing Contract and Component (CORE-002)

> Status: **COMPLETE**

---

## Summary

Implemented a Ports & Adapters password hashing component: a `PasswordHasher` interface in Core layer and a
`BcryptPasswordHasher` implementation in Infrastructure layer. This follows the same pattern as
`Serializer` -> `FractalSerializer`.

---

## Files Created

| File | Purpose |
|------|---------|
| `src/Core/Security/PasswordHasher.php` | Port interface with `hash()`, `verify()`, `needsRehash()` |
| `src/Infrastructure/Security/BcryptPasswordHasher.php` | Bcrypt adapter using PHP native `password_*` functions |
| `config/common/security.php` | DI wiring: interface -> implementation (cost=12 for production) |
| `config/dev/security.php` | Dev override (cost=4 for speed) |
| `config/test/security.php` | Test override (cost=4 for speed) |
| `tests/Unit/Infrastructure/Security/BcryptPasswordHasherCest.php` | 7 unit tests covering all methods |

## Files Modified

| File | Change |
|------|--------|
| `docs/02-onboarding/03-structure.md` | Added `Core/Security/` and `Infrastructure/Security/` to directory tree |

---

## Usage

Inject `PasswordHasher` interface via DI:

```php
use Bgl\Core\Security\Hasher;

final readonly class SomeHandler
{
    public function __construct(
        private Hasher $hasher,
    ) {}

    public function handle(string $plainPassword): string
    {
        return $this->hasher->hash($plainPassword);
    }
}
```

---

## Configuration

Bcrypt cost parameter per environment:

| Environment | Cost | Config File |
|-------------|------|-------------|
| Production | 12 | `config/common/security.php` |
| Development | 4 | `config/dev/security.php` |
| Test | 4 | `config/test/security.php` |

---

## Test Coverage

7 unit tests in `BcryptPasswordHasherCest`:

1. `testHashReturnsNonEmptyString` -- hash output is non-empty
2. `testHashReturnsDifferentStringFromPlainPassword` -- hash differs from input
3. `testVerifyCorrectPassword` -- correct password verifies
4. `testVerifyWrongPassword` -- wrong password rejected
5. `testNeedsRehashReturnsFalseForFreshHash` -- fresh hash does not need rehash
6. `testNeedsRehashReturnsTrueForDifferentCost` -- cost change detected
7. `testHashProducesDifferentHashesForSamePassword` -- salt uniqueness

---

## Architectural Notes

- `PasswordHasher` interface in Core has zero dependencies (Deptrac verified)
- `BcryptPasswordHasher` depends only on Core (via interface) and PHP native functions
- DI config uses factory + string alias pattern (same as serializer.php)
- Environment-specific configs override only the factory, keeping the interface alias in common
