# PROFILE-001: Master Checklist

**Beads:** bgl-q77

## Requirements Quality

| Category | Status | Notes |
|----------|--------|-------|
| Completeness | OK | All requirements present |
| Clarity | OK | Format, examples, layer placement defined |
| Consistency | OK | Aligns with existing UuidGenerator pattern |
| Measurability | OK | Acceptance criteria are objectively verifiable |
| Scenario Coverage | OK | API endpoint + registration + bug fix flows covered |
| Edge Case Coverage | OK | Legacy null names addressed with fallback |
| Non-Functional | OK | Rate limiting deferred to post-MVP (clarified) |

## Progress

- [ ] Stage 1: Core interface + Infrastructure implementation [P]
- [ ] Stage 2: Fix User entity bug [P]
- [ ] Stage 3: Application layer (Query + Handler) + DI + OpenAPI route
- [ ] Stage 4: Integrate Nomenclator into registration
- [ ] Stage 5: Tests + Quality gates

## Stage Details

### Stage 1: Core interface + Infrastructure implementation [P]

**~20 min** | No dependencies | Creates `Nomenclator` interface and `RandomNomenclator`

Files:
- CREATE `src/Core/Identity/Nomenclator.php`
- CREATE `src/Infrastructure/Identity/RandomNomenclator.php`
- MODIFY `config/common/persistence.php` -- DI binding

Reference: `src/Core/Identity/UuidGenerator.php`, `src/Infrastructure/Identity/RamseyUuidGenerator.php`

### Stage 2: Fix User entity bug [P]

**~15 min** | No dependencies | Fix `getName()` non-determinism, remove `generateDefaultName()`

Files:
- MODIFY `src/Domain/Profile/Entities/User.php` -- remove `generateDefaultName()`, fix `getName()`

### Stage 3: Application layer + OpenAPI route

**~25 min** | Depends on Stage 1 | Query, Handler + OpenAPI config + bus registration

Files:
- CREATE `src/Application/Handlers/Profile/GenerateNickname/Query.php`
- CREATE `src/Application/Handlers/Profile/GenerateNickname/Handler.php`
- CREATE `config/common/openapi/profile.php`
- MODIFY `config/common/bus.php` -- register handler

Reference: `src/Application/Handlers/User/GetUser/` (Query/Handler pattern)

### Stage 4: Integrate Nomenclator into registration

**~15 min** | Depends on Stage 1, 2 | Inject Nomenclator into Register\Handler

Files:
- MODIFY `src/Application/Handlers/Auth/Register/Handler.php` -- add Nomenclator dependency

### Stage 5: Tests + Quality gates

**~30 min** | Depends on all stages | Unit + Functional + Web tests, run `make scan`

Files:
- CREATE `tests/Unit/Infrastructure/Identity/RandomNomenclatorCest.php`
- CREATE `tests/Unit/Domain/Profile/Entities/UserCest.php`
- CREATE `tests/Functional/Profile/GenerateNicknameCest.php`
- MODIFY `tests/Functional/Auth/RegisterCest.php` -- test registration with generated name
- CREATE `tests/Web/ProfileCest.php`

## Consistency Analysis

| ID | Severity | Location | Issue | Recommendation |
|----|----------|----------|-------|----------------|
| -- | -- | -- | No issues found | -- |

Metrics: 5 requirements, 5 stages, 100% coverage
