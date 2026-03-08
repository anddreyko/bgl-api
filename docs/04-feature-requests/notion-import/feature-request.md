# SYNC-004: Import data from Notion export

## Context

Historical board game session data is stored in a Notion database, exported as CSV to `var/notion-export/`.
This data needs to be imported into the BGL database to serve as the initial dataset.

## Source data (var/notion-export/Private & Shared/Log/)

### Sessions CSV (~278 sessions, 2021-2026)

- Columns: Name, Created at, Date, Game, Hours, Notes, Place, Players, Victory, Winner
- Date format: "June 12, 2021" (English month names)
- Game: name + link to Games table, e.g. `Descent: Journeys in the Dark (Games/...html)`
- Players: links to Winners table, e.g. `Альянс (Winners/...html), Орда (Winners/...html)`
- Winner: single link to Winners table
- Victory: Yes/No (coop win/loss)
- Hours: float (e.g. 2.5) or empty
- Notes: multi-line text with game details
- Place: link to Arcades table

### Games CSV (37 games)

- Columns: Name, Alex (rating 1-10), BGG (URL), Drew (emoji rating), First Play, Recent Play, Russian name, Tags,
  sessions count, hours
- BGG URL format: `https://boardgamegeek.com/boardgame/{bgg_id}`
- Some games have Russian names, some don't

### Winners CSV (8 entries)

- Columns: name, Type (pvp/coop), played, sessions won, won
- Real people (Type=pvp): Саша, Андрей, Коля, Аня, Лёня, Random Online Player
- Teams (Type=coop): Альянс (alliance of players), Орда (automa/AI opponent)
- Андрей = owner user, not a mate

### Arcades CSV (17+ places)

- Columns: Name, Address, Games, Notes, Price, Sessions, URL

## Target mapping

| Source           | Target table  | Notes                                                                     |
|------------------|---------------|---------------------------------------------------------------------------|
| Games CSV        | games_game    | Extract bgg_id from BGG URL                                               |
| Winners          | mates_mate    | 5 mates:Андрей, Саша, Коля, Аня, Лёня, Random Online Player aka Anonymous |
| Arcades CSV      | places_place  | New entity (PLACES-001)                                                   |
| Sessions CSV     | plays_session | Link game_id by game name, place_id by place name                         |
| Sessions.Players | plays_player  | team_tag for coop teams, is_winner from Winner/Victory                    |

## Player/team mapping logic

Sessions have Players field with mix of individuals and teams:

- `('Андрей', 'Саша')` -- pvp, create plays_player for Саша (Андрей = owner)
- `('Альянс', 'Орда')` -- coop with no individual names in Players field
- `('Андрей', 'Орда', 'Саша')` -- mixed, Андрей+Саша vs Орда (automa)

Team tag assignment:

- Альянс members -> team_tag = 'alliance'
- Орда -> team_tag = 'horde' (automa, create as mate? or skip)
- Free-for-all (pvp without teams) -> team_tag = NULL

is_winner logic:

- Winner = specific person -> that person's is_winner = true
- Winner = Альянс -> all alliance players get is_winner = true
- Winner = Орда -> all alliance players get is_winner = false
- Victory = Yes + Winner = Альянс -> coop victory

## Dependencies (blockers)

- **bgl-ajx** (PLAYS-007): Add team_tag and number to Player
- **bgl-q3w** (PLAYS-008): Add notes to Play session
- **bgl-ddw** (PLACES-001): Create Place entity

## Implementation

PHP script: `var/notion-import.php` generates SQL.
Existing draft: parses CSVs, generates INSERT statements with ON CONFLICT DO NOTHING.
Needs update after blockers are resolved to include team_tag, notes, place_id.

## Production migration

Separate plan exists for production DB migration (Clever Cloud):

- See `.claude/plans/majestic-questing-sedgewick.md`
- 65 sessions already on production (from old records_session table)
- Production import after local validation
