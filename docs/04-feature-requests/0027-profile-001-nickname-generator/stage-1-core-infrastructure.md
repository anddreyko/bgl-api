# Stage 1: Core Interface + Infrastructure Implementation [P]

## Overview

Create the `Nomenclator` interface in Core and `RandomNomenclator` implementation in Infrastructure. Register DI binding. Follows the same pattern as `UuidGenerator` / `RamseyUuidGenerator`.

## Dependencies

None -- can run in parallel with Stage 2.

## Implementation Steps

### 1.1 Create Nomenclator interface

File: `src/Core/Identity/Nomenclator.php`

```php
interface Nomenclator
{
    public function generate(): string;
}
```

Pattern reference: `src/Core/Identity/UuidGenerator.php`

### 1.2 Create RandomNomenclator

File: `src/Infrastructure/Identity/RandomNomenclator.php`

- Constants: `ADJECTIVES` (~15 words), `NOUNS` (~15 board-game words)
- `generate()`: pick random adjective + noun, optionally append number (0-99, ~50% chance)
- Use `random_int()` for selection
- Format: PascalCase concatenation, e.g. `EpicDice42`

Word lists (English only):
- Adjectives: Epic, Mighty, Clever, Swift, Bold, Grand, Noble, Royal, Brave, Ancient, Wise, Lucky, Fierce, Silent, Dark
- Nouns: Dice, Meeple, Knight, Wizard, Dragon, Castle, Board, Token, Card, Quest, Guild, Tavern, Rogue, Baron, Pawn

### 1.3 Register DI binding

File: `config/common/persistence.php`

Add line (same pattern as UuidGenerator on line 26):
```php
Nomenclator::class => static fn(RandomNomenclator $n): Nomenclator => $n,
```

## Files to Create/Modify

| File | Action |
|------|--------|
| `src/Core/Identity/Nomenclator.php` | CREATE |
| `src/Infrastructure/Identity/RandomNomenclator.php` | CREATE |
| `config/common/persistence.php` | MODIFY (add DI binding) |

## Completion Criteria

- Interface exists with `generate(): string` method
- Implementation returns board-game-themed names matching format `{Adjective}{Noun}[{Number}]`
- DI container resolves `Nomenclator` to `RandomNomenclator`

## Verification

```bash
composer lp:run
composer ps:run src/Core/Identity/Nomenclator.php src/Infrastructure/Identity/RandomNomenclator.php
```

## Potential Issues

- Psalm may require `@return non-empty-string` annotation -- add if needed
