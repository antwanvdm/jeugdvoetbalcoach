# Database Schema

Dit document beschrijft de database structuur van de VVOR Team Manager applicatie.

## Overzicht

De database bestaat uit de volgende hoofdtabellen:

-   **users** - Gebruikers van het systeem
-   **players** - Spelers in het team
-   **positions** - Voetbalposities (Keeper, Verdediger, etc.)
-   **opponents** - Tegenstanders
-   **formations** - Formatie presets met `total_players` en `lineup_formation`
-   **seasons** - Seizoenen die een formatie refereren
-   **football_matches** - Wedstrijden (horen bij een seizoen)
-   **football_match_player** - Pivot tabel voor spelers per wedstrijd/kwart

## 📊 Entity Relationship Diagram

```
┌─────────────┐    ┌─────────────────┐    ┌──────────────┐
│    users    │    │    players      │    │  positions   │
├─────────────┤    ├─────────────────┤    ├──────────────┤
│ id (PK)     │    │ id (PK)         │    │ id (PK)      │
│ name        │    │ name            │────┤ name         │
│ email       │    │ position_id (FK)│    │ created_at   │
│ password    │    │ weight          │    │ updated_at   │
│ created_at  │    │ created_at      │    └──────────────┘
│ updated_at  │    │ updated_at      │
└─────────────┘    └─────────────────┘
                            │
                            │
       ┌─────────────────────┼─────────────────────┐
       │                     │                     │
┌─────────────────┐    ┌─────────────────────────────────┐
│   opponents     │    │   football_match_player         │
├─────────────────┤    ├─────────────────────────────────┤
│ id (PK)         │    │ football_match_id (FK)          │
│ name            │    │ player_id (FK)                  │
│ location        │    │ quarter                         │
│ logo            │    │ position_id (FK) [nullable]     │
│ latitude        │    │ created_at                      │
│ longitude       │    │ updated_at                      │
│ created_at      │    └─────────────────────────────────┘
│ updated_at      │
└─────────────────┘                 │
       │                            │
       │                            │
┌─────────────────┐                 │
│football_matches │                 │
├─────────────────┤                 │
│ id (PK)         │─────────────────┘
│ season_id (FK)  │
│ opponent_id (FK)│
│ home (boolean)  │
│ goals_scored    │
│ goals_conceded  │
│ date            │
│ created_at      │
│ updated_at      │
└─────────────────┘
       │
       │
┌─────────────┐
│  seasons    │
├─────────────┤
│ id (PK)     │
│ formation_id│ (FK)
│ start/end   │
│ year/part   │
└─────────────┘
       │
       │
┌──────────────┐
│  formations  │
├──────────────┤
│ id (PK)      │
│ total_players│
│ lineup_form. │
└──────────────┘
```

## 📋 Tabellen

### users

Standaard Laravel gebruikerstabel voor authenticatie.

| Kolom               | Type            | Nullable | Default        | Beschrijving          |
| ------------------- | --------------- | -------- | -------------- | --------------------- |
| `id`                | bigint unsigned | NO       | AUTO_INCREMENT | Primary key           |
| `name`              | varchar(255)    | NO       |                | Gebruikersnaam        |
| `email`             | varchar(255)    | NO       |                | E-mailadres (uniek)   |
| `email_verified_at` | timestamp       | YES      | NULL           | Verificatie timestamp |
| `password`          | varchar(255)    | NO       |                | Gehashed wachtwoord   |
| `remember_token`    | varchar(100)    | YES      | NULL           | Remember me token     |
| `created_at`        | timestamp       | YES      | NULL           | Aanmaakdatum          |
| `updated_at`        | timestamp       | YES      | NULL           | Laatste wijziging     |

**Indexen:**

-   PRIMARY KEY (`id`)
-   UNIQUE KEY (`email`)

---

### positions

Voetbalposities die spelers kunnen spelen.

| Kolom        | Type            | Nullable | Default        | Beschrijving        |
| ------------ | --------------- | -------- | -------------- | ------------------- |
| `id`         | bigint unsigned | NO       | AUTO_INCREMENT | Primary key         |
| `name`       | varchar(255)    | NO       |                | Naam van de positie |
| `created_at` | timestamp       | YES      | NULL           | Aanmaakdatum        |
| `updated_at` | timestamp       | YES      | NULL           | Laatste wijziging   |

**Standaard data:**

1. Keeper
2. Verdediger
3. Middenvelder
4. Aanvaller

**Indexen:**

-   PRIMARY KEY (`id`)

---

### players

Alle spelers in het team met hun eigenschappen.

| Kolom         | Type            | Nullable | Default        | Beschrijving           |
| ------------- | --------------- | -------- | -------------- | ---------------------- |
| `id`          | bigint unsigned | NO       | AUTO_INCREMENT | Primary key            |
| `name`        | varchar(255)    | NO       |                | Naam van de speler     |
| `position_id` | bigint unsigned | NO       |                | Favoriete/hoofdpositie |
| `weight`      | tinyint         | NO       | 1              | Fysiek niveau (1-5)    |
| `created_at`  | timestamp       | YES      | NULL           | Aanmaakdatum           |
| `updated_at`  | timestamp       | YES      | NULL           | Laatste wijziging      |

**Weight systeem:**

-   `1` = Laag fysiek niveau
-   `2` = Onder gemiddeld
-   `3` = Gemiddeld
-   `4` = Boven gemiddeld
-   `5` = Hoog fysiek niveau

**Foreign Keys:**

-   `position_id` → `positions(id)`

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`position_id`)

---

### opponents

Tegenstanders waartegen gespeeld wordt.

| Kolom        | Type            | Nullable | Default        | Beschrijving                 |
| ------------ | --------------- | -------- | -------------- | ---------------------------- |
| `id`         | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                  |
| `name`       | varchar(255)    | NO       |                | Naam van de tegenstander     |
| `location`   | varchar(255)    | NO       |                | Plaatsnaam                   |
| `logo`       | varchar(255)    | YES      | NULL           | URL naar logo afbeelding     |
| `latitude`   | decimal(10,8)   | NO       |                | Breedtegraad                 |
| `longitude`  | decimal(11,8)   | NO       |                | Lengtegraad                  |
| `created_at` | timestamp       | YES      | NULL           | Aanmaakdatum                 |
| `updated_at` | timestamp       | YES      | NULL           | Laatste wijziging            |

**Indexen:**

-   PRIMARY KEY (`id`)

---

### football_matches

Wedstrijden met resultaten en metadata.

| Kolom            | Type            | Nullable | Default        | Beschrijving            |
| ---------------- | --------------- | -------- | -------------- | ----------------------- |
| `id`             | bigint unsigned | NO       | AUTO_INCREMENT | Primary key             |
| `opponent_id`    | bigint unsigned | NO       |                | Tegenstander            |
| `home`           | tinyint(1)      | NO       |                | Thuis (1) of uit (0)    |
| `goals_scored`   | int unsigned    | YES      | NULL           | Doelpunten gescoord     |
| `goals_conceded` | int unsigned    | YES      | NULL           | Doelpunten tegengekrgen |
| `date`           | datetime        | NO       |                | Wedstrijddatum en tijd  |
| `created_at`     | timestamp       | YES      | NULL           | Aanmaakdatum            |
| `updated_at`     | timestamp       | YES      | NULL           | Laatste wijziging       |

**Computed properties:**

-   `result` - Berekend: 'W' (winst), 'L' (verlies), 'D' (gelijk), 'O' (open)

**Foreign Keys:**

-   `opponent_id` → `opponents(id)`

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`opponent_id`)
-   INDEX (`date`)

---

### football_match_player

Pivot tabel die spelers koppelt aan wedstrijden per kwart.

| Kolom               | Type             | Nullable | Default | Beschrijving                       |
| ------------------- | ---------------- | -------- | ------- | ---------------------------------- |
| `football_match_id` | bigint unsigned  | NO       |         | Wedstrijd referentie               |
| `player_id`         | bigint unsigned  | NO       |         | Speler referentie                  |
| `quarter`           | tinyint unsigned | NO       |         | Kwart (1-4)                        |
| `position_id`       | bigint unsigned  | YES      | NULL    | Positie in dit kwart (NULL = bank) |
| `created_at`        | timestamp        | YES      | NULL    | Aanmaakdatum                       |
| `updated_at`        | timestamp        | YES      | NULL    | Laatste wijziging                  |

**Belangrijke opmerkingen:**

-   `position_id = NULL` betekent dat de speler op de bank zit
-   `position_id` gevuld betekent dat de speler speelt op die positie
-   Elke speler kan meerdere records hebben per wedstrijd (één per kwart)

**Foreign Keys:**

-   `football_match_id` → `football_matches(id)` ON DELETE CASCADE
-   `player_id` → `players(id)` ON DELETE CASCADE
-   `position_id` → `positions(id)` ON DELETE SET NULL

**Indexen:**

-   INDEX (`football_match_id`, `player_id`, `quarter`) - Composite voor queries
-   INDEX (`player_id`)
-   INDEX (`position_id`)

---

## 🔄 Relaties

### One-to-Many

-   `positions` → `players` (Een positie heeft meerdere spelers)
-   `opponents` → `football_matches` (Een tegenstander heeft meerdere wedstrijden)

### Many-to-Many

-   `players` ↔ `football_matches` via `football_match_player`
    -   Extra data: `quarter`, `position_id`

### Polymorphic

Geen polymorphic relaties in de huidige structuur.

## 📈 Query Patterns

### Veelgebruikte queries

**Alle spelers met keeper statistieken:**

```sql
SELECT p.*, COUNT(fmp.id) as keeper_count
FROM players p
LEFT JOIN football_match_player fmp ON p.id = fmp.player_id AND fmp.position_id = 1
GROUP BY p.id;
```

**Line-up voor een specifiek kwart:**

```sql
SELECT p.name, pos.name as position, fmp.quarter
FROM football_match_player fmp
JOIN players p ON fmp.player_id = p.id
LEFT JOIN positions pos ON fmp.position_id = pos.id
WHERE fmp.football_match_id = ? AND fmp.quarter = ?
ORDER BY fmp.position_id IS NULL, pos.name;
```

**Keepers van laatste wedstrijd:**

```sql
SELECT DISTINCT p.id, p.name
FROM players p
JOIN football_match_player fmp ON p.id = fmp.player_id
JOIN football_matches fm ON fmp.football_match_id = fm.id
WHERE fmp.position_id = 1
ORDER BY fm.date DESC
LIMIT 4;
```

## 🚀 Performance Overwegingen

### Indexering

-   Alle foreign keys zijn geïndexeerd
-   Composite index op `football_match_player` voor efficiënte line-up queries
-   Date index op `football_matches` voor chronologische queries

### Query Optimalisatie

-   Gebruik van `withCount()` voor aggregatie queries
-   Eager loading voor N+1 query preventie
-   Specifieke select statements waar mogelijk

### Caching Strategie

-   Model caching voor `positions` (wijzigt zelden)
-   Query caching voor statistiek overzichten
-   Page caching voor wedstrijd overzichten

---

## 🔧 Migraties

Migraties zijn te vinden in `/database/migrations/` en worden uitgevoerd in chronologische volgorde:

1. `0001_01_01_000000_create_users_table.php`
2. `0001_01_01_000001_create_cache_table.php`
3. `0001_01_01_000002_create_jobs_table.php`
4. `2025_09_24_121108_create_opponents_table.php`
5. `2025_09_24_121127_create_positions_table.php`
6. `2025_09_24_121128_create_players_table.php`
7. `2025_09_24_121228_create_football_matches_table.php`
8. `2025_09_24_121404_create_football_match_player_table.php`
9. `2025_09_30_080406_add_weigth_to_players_table.php`

Voor een fresh installatie:

```bash
php artisan migrate:fresh --seed
```
