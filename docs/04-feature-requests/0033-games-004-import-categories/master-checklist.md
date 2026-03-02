# GAMES-004: Import Game Categories from BGG -- Master Checklist

## Stage 1: Domain

- [ ] Add `categories` property to `Game` entity: `/** @var list<string> */ private array $categories = []`
- [ ] Add getter `getCategories(): array` and method `updateCategories(array $categories): void`
- [ ] Backward compatible: default empty array, nullable in DB

## Stage 2: Infrastructure

- [ ] Migration: ALTER TABLE games_game ADD COLUMN categories JSONB DEFAULT NULL
- [ ] Update Game Doctrine mapping: map categories as json type
- [ ] Update `XmlFieldExtractor` to parse `<link type="boardgamecategory">` elements
- [ ] Update `BggGames` adapter to pass categories to Game factory/update
- [ ] Update serialization config for Games\GetGame\Result to include categories

## Stage 3: Tests

- [ ] Unit test: Game.updateCategories()
- [ ] Functional test: BggGames adapter parses categories from XML
- [ ] Functional test: Game persisted with categories via Doctrine
- [ ] Web test: GET /v1/games/{id} includes categories in response

## Validation

- [ ] make scan passes
- [ ] All existing tests green
- [ ] New tests green
