# Master Checklist: Password Hashing Contract and Component (CORE-002)

> Beads: bgl-w52
> Status: **COMPLETE**

---

## Stage 1: Core Contract (~10min)

**Dependencies:** None

- [x] Create `src/Core/Security/PasswordHasher.php` -- interface
  - `hash(string $plainPassword): string`
  - `verify(string $plainPassword, string $hashedPassword): bool`
  - `needsRehash(string $hashedPassword): bool`
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File | Action |
|------|--------|
| `src/Core/Security/PasswordHasher.php` | CREATE |

---

## Stage 2: Infrastructure Implementation (~15min)

**Dependencies:** Stage 1

- [x] Create `src/Infrastructure/Security/BcryptPasswordHasher.php`
  - `hash()` via `password_hash($password, PASSWORD_BCRYPT)`
  - `verify()` via `password_verify()`
  - `needsRehash()` via `password_needs_rehash()`
- [x] Wire DI in `config/common/security.php`: `PasswordHasher::class => BcryptPasswordHasher::class`
- [x] Weakest algo params in `config/dev/security.php` and `config/test/security.php`
- [x] Verify: `composer lp:run && composer ps:run`

### Files

| File                                                   | Action |
|--------------------------------------------------------|--------|
| `src/Infrastructure/Security/BcryptPasswordHasher.php` | CREATE |
| `config/common/security.php`                           | CREATE |
| `config/dev/security.php`                              | CREATE |
| `config/test/security.php`                             | CREATE |

---

## Stage 3: Tests (~20min)

**Dependencies:** Stage 2

- [x] Unit test: `BcryptPasswordHasher::hash()` -- returns non-empty string, different from plain password
- [x] Unit test: `BcryptPasswordHasher::verify()` -- correct password returns true, wrong returns false
- [x] Unit test: `BcryptPasswordHasher::needsRehash()` -- fresh hash returns false, different cost returns true
- [x] Unit test: salt uniqueness -- two hashes of same password differ
- [x] Verify: `composer test:unit`

### Files

| File | Action |
|------|--------|
| `tests/Unit/Infrastructure/Security/BcryptPasswordHasherCest.php` | CREATE |

---

## Stage 4: Final Validation (~10min)

**Dependencies:** All previous stages

- [x] Run `composer lp:run` -- passed
- [x] Run `composer ps:run` -- passed (for security files)
- [x] Run `composer dt:run` -- 0 violations
- [x] Run `composer test:unit` -- 39 tests, 55 assertions, all passed
- [x] Review: PSR-12, `declare(strict_types=1)` in all files
- [x] `composer scan:all` -- pre-existing shadow dependency issues only (not related to this task)

---

## Code References

| File | What to Learn |
|------|---------------|
| `src/Core/Serialization/Serializer.php` | Same Ports & Adapters pattern |
| `src/Infrastructure/Serialization/FractalSerializer.php` | Implementation pattern |
| `config/common/serializer.php` | DI wiring pattern |
