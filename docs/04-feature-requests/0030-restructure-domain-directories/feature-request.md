# Restructure Domain Directories to Flat Context Layout

## Problem

All 4 bounded contexts use `Domain/{Context}/Entities/` subdirectory, violating the adopted convention
(08-code-conventions.md section 13): aggregate root at context root, child entities in subdirectories.

## Current Structure

```
Domain/Games/Entities/{Game,Games}.php
Domain/Mates/Entities/{Mate,Mates}.php
Domain/Plays/Entities/{Play,Plays,Player,Players,PlayersFactory,EmptyPlayers,PlayStatus,Visibility}.php
Domain/Profile/Entities/{User,Users,UserId,UserStatus,Passkey,Passkeys,PasskeyChallenge,PasskeyChallenges}.php
Domain/Profile/Exceptions/UserAlreadyExistsException.php
```

## Target Structure

```
Domain/Games/{Game,Games}.php
Domain/Mates/{Mate,Mates}.php
Domain/Plays/{Play,Plays,PlayStatus,Visibility}.php
Domain/Plays/Player/{Player,Players,PlayersFactory,EmptyPlayers}.php
Domain/Profile/{User,Users,UserId,UserStatus,UserAlreadyExistsException}.php
Domain/Profile/Passkey/{Passkey,Passkeys,PasskeyChallenge,PasskeyChallenges}.php
```

## Impact

- 21 PHP files moved
- ~225 import statements updated (106 in src, 108 in tests, 9 in config, 2 for Exceptions)
- Doctrine mapping files reference Domain namespaces
- DI config references Domain classes
- Deptrac rules may reference paths
- PSR-4 autoload in composer.json (no change needed -- maps `Bgl\Domain\` to `src/Domain/`)

## Acceptance Criteria

- All files moved to target locations
- All namespaces updated
- All imports across entire codebase updated
- `composer dump-autoload` succeeds
- `composer scan:all` passes
- All test suites pass
