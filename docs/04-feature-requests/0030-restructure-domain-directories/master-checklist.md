# Master Checklist: Restructure Domain Directories

## Stage 1: Games Context (simplest, 2 files)

- [ ] Move `Domain/Games/Entities/Game.php` -> `Domain/Games/Game.php`
- [ ] Move `Domain/Games/Entities/Games.php` -> `Domain/Games/Games.php`
- [ ] Update namespace `Bgl\Domain\Games\Entities` -> `Bgl\Domain\Games`
- [ ] Update all imports in src/ (4 files, ~7 occurrences)
- [ ] Update all imports in tests/ (~6 files)
- [ ] Update all imports in config/ (2 files)
- [ ] Update Doctrine mapping (`GameMapping.php`)
- [ ] Remove empty `Domain/Games/Entities/` directory
- [ ] Run `composer dump-autoload`
- [ ] Run `composer ps:run` -- must pass
- [ ] Run `composer test:unit` -- must pass

## Stage 2: Mates Context (2 files)

- [ ] Move `Domain/Mates/Entities/Mate.php` -> `Domain/Mates/Mate.php`
- [ ] Move `Domain/Mates/Entities/Mates.php` -> `Domain/Mates/Mates.php`
- [ ] Update namespace `Bgl\Domain\Mates\Entities` -> `Bgl\Domain\Mates`
- [ ] Update all imports in src/ (~6 files)
- [ ] Update all imports in tests/ (~5 files)
- [ ] Update Doctrine mapping (`MateMapping.php`)
- [ ] Remove empty `Domain/Mates/Entities/` directory
- [ ] Run `composer ps:run` -- must pass
- [ ] Run `composer test:unit` -- must pass

## Stage 3: Plays Context (8 files, child entity Player)

- [ ] Move aggregate root files to `Domain/Plays/`:
  - `Play.php`, `Plays.php`, `PlayStatus.php`, `Visibility.php`
- [ ] Move child entity files to `Domain/Plays/Player/`:
  - `Player.php`, `Players.php`, `PlayersFactory.php`, `EmptyPlayers.php`
- [ ] Update namespace `Bgl\Domain\Plays\Entities` -> `Bgl\Domain\Plays` (aggregate)
- [ ] Update namespace `Bgl\Domain\Plays\Entities` -> `Bgl\Domain\Plays\Player` (child entity)
- [ ] Update all imports in src/ (~20 files)
- [ ] Update all imports in tests/ (~15 files)
- [ ] Update Doctrine mapping (`PlayMapping.php`, `PlayerMapping.php`)
- [ ] Update DI config (`config/common/persistence.php`)
- [ ] Remove empty `Domain/Plays/Entities/` directory
- [ ] Run `composer ps:run` -- must pass
- [ ] Run `composer test:unit` -- must pass
- [ ] Run `composer test:func` -- must pass (DB-related)

## Stage 4: Profile Context (9 files, child entity Passkey, exception)

- [ ] Move aggregate root files to `Domain/Profile/`:
  - `User.php`, `Users.php`, `UserId.php`, `UserStatus.php`
- [ ] Move `UserAlreadyExistsException.php` from `Domain/Profile/Exceptions/` to `Domain/Profile/`
- [ ] Move child entity files to `Domain/Profile/Passkey/`:
  - `Passkey.php`, `Passkeys.php`, `PasskeyChallenge.php`, `PasskeyChallenges.php`
- [ ] Update namespace `Bgl\Domain\Profile\Entities` -> `Bgl\Domain\Profile` (aggregate)
- [ ] Update namespace `Bgl\Domain\Profile\Exceptions` -> `Bgl\Domain\Profile` (exception)
- [ ] Update namespace `Bgl\Domain\Profile\Entities` -> `Bgl\Domain\Profile\Passkey` (child)
- [ ] Update all imports in src/ (~20 files)
- [ ] Update all imports in tests/ (~15 files)
- [ ] Update all imports in config/ (3 files)
- [ ] Update Doctrine mapping (UserMapping, PasskeyMapping, PasskeyChallengeMapping)
- [ ] Remove empty `Domain/Profile/Entities/` and `Domain/Profile/Exceptions/` directories
- [ ] Run `composer ps:run` -- must pass
- [ ] Run `composer test:unit` -- must pass
- [ ] Run `composer test:func` -- must pass

## Stage 5: Final Validation

- [ ] `composer dump-autoload`
- [ ] `composer scan:all` -- MUST pass
- [ ] `composer test:all` -- MUST pass
- [ ] No remaining references to old namespaces (grep check)
- [ ] Update `AGENTS.md` section 3 Project Structure
- [ ] Update `docs/02-onboarding/03-structure.md` if exists
- [ ] Update deptrac config if references old paths

## Validation Criteria

- Zero references to `Domain\*\Entities\` namespace anywhere in codebase
- Zero references to `Domain\Profile\Exceptions\` namespace
- All quality gates pass (`scan:all`)
- All test suites pass (`test:all`)
