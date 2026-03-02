# GAMES-004: Import Game Categories from BGG

## Summary

Extend Game entity with categories imported from BoardGameGeek API.
Categories are stored as a list of strings (e.g., "Card Game", "Fantasy", "Puzzle").
Populated on game import/update from BGG.

## Requirements

1. Game entity gains `categories` field (list of strings, nullable for backward compat)
2. BGG adapter parses `<boardgamecategory>` elements from BGG XML API
3. Categories stored in database (JSONB column or join table)
4. Existing games without categories remain valid (nullable)
5. No new API endpoint -- categories exposed via existing GET /v1/games/{id}

## Technical Notes

- BGG XML API returns categories as `<link type="boardgamecategory" value="Card Game"/>`
- BggGames adapter and XmlFieldExtractor need updates for category parsing
- Migration: ALTER TABLE games_game ADD COLUMN categories JSONB DEFAULT NULL
- Update GetGame Result to include categories field
- Backfill: optional CLI command to re-fetch categories for existing games
