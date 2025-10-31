# Database Schema

Dit document beschrijft de database structuur van de VVOR Team Manager applicatie.

## Overzicht

De database ondersteunt een **multi-user architectuur** waarbij elke gebruiker zijn eigen team beheert. Admins hebben volledige toegang, reguliere gebruikers zien alleen hun eigen data.

De database bestaat uit de volgende hoofdtabellen:

-   **users** - Gebruikers met rollen (admin/user) en teamprofielen
-   **players** - Spelers per gebruiker
-   **positions** - Voetbalposities (globaal, gedeeld)
-   **opponents** - Tegenstanders per gebruiker
-   **formations** - Formatie presets (globaal of per gebruiker)
-   **seasons** - Seizoenen per gebruiker
-   **football_matches** - Wedstrijden per gebruiker
-   **football_match_player** - Pivot tabel voor line-ups

## 📊 Entity Relationship Diagram

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ email           │
│ password        │
│ role            │ (1=admin, 2=user)
│ team_name       │
│ maps_location   │
│ logo            │
│ is_active       │
└─────────────────┘
        │
        │ (owns multiple)
        ├─────────────────────┬──────────────────┬───────────────┐
        │                     │                  │               │
        ▼                     ▼                  ▼               ▼
┌─────────────────┐   ┌─────────────┐   ┌──────────────┐  ┌─────────────┐
│    players      │   │  opponents  │   │  formations  │  │   seasons   │
├─────────────────┤   ├─────────────┤   ├──────────────┤  ├─────────────┤
│ id (PK)         │   │ id (PK)     │   │ id (PK)      │  │ id (PK)     │
│ user_id (FK)    │   │ user_id (FK)│   │ user_id (FK) │  │ user_id (FK)│
│ name            │   │ name        │   │ total_players│  │ formation_id│
│ position_id (FK)│   │ location    │   │ lineup_form. │  │ start/end   │
│ weight          │   │ logo        │   │ is_global    │  │ year/part   │
└─────────────────┘   │ latitude    │   └──────────────┘  └─────────────┘
        │             │ longitude   │
        │             └─────────────┘
        │                     │
        │                     │
        │             ┌───────┘
        │             │
┌───────────────┐     │
│  positions    │     │
├───────────────┤     │
│ id (PK)       │     │
│ name          │     │
└───────────────┘     │
        │             │
        └─────┬───────┘
              │
              ▼
┌─────────────────────────────────┐
│   football_matches              │
├─────────────────────────────────┤
│ id (PK)                         │
│ user_id (FK)                    │
│ season_id (FK)                  │
│ opponent_id (FK)                │
│ home (boolean)                  │
│ goals_scored / goals_conceded   │
│ date                            │
└─────────────────────────────────┘
              │
              │ (line-up details)
              ▼
┌─────────────────────────────────┐
│   football_match_player         │
├─────────────────────────────────┤
│ football_match_id (FK)          │
│ user_id (FK)                    │
│ player_id (FK)                  │
│ quarter (1-4)                   │
│ position_id (FK) [nullable]     │
└─────────────────────────────────┘
```

## 📋 Tabellen

### users

Gebruikers van het systeem met rollen en teamprofielen.

| Kolom               | Type            | Nullable | Default        | Beschrijving                    |
| ------------------- | --------------- | -------- | -------------- | ------------------------------- |
| `id`                | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                     |
| `name`              | varchar(255)    | NO       |                | Gebruikersnaam                  |
| `email`             | varchar(255)    | NO       |                | E-mailadres (uniek)             |
| `email_verified_at` | timestamp       | YES      | NULL           | Verificatie timestamp           |
| `password`          | varchar(255)    | NO       |                | Gehashed wachtwoord             |
| `role`              | tinyint         | NO       | 2              | Rol (1=admin, 2=user)           |
| `team_name`         | varchar(255)    | YES      | NULL           | Naam van het team               |
| `maps_location`     | varchar(255)    | YES      | NULL           | Google Maps locatie/coördinaten |
| `logo`              | varchar(255)    | YES      | NULL           | URL naar team logo              |
| `is_active`         | boolean         | NO       | true           | Actieve status                  |
| `remember_token`    | varchar(100)    | YES      | NULL           | Remember me token               |
| `created_at`        | timestamp       | YES      | NULL           | Aanmaakdatum                    |
| `updated_at`        | timestamp       | YES      | NULL           | Laatste wijziging               |

**Role systeem:**

-   `1` = Admin - Volledige toegang, kan gebruikers beheren
-   `2` = User - Standaard gebruiker, beheert eigen team

**Relaties:**

-   Heeft meerdere `players`, `opponents`, `formations`, `seasons`, `football_matches`

**Indexen:**

-   PRIMARY KEY (`id`)
-   UNIQUE KEY (`email`)

---

### positions

Voetbalposities die spelers kunnen spelen. **Globale tabel**, niet per gebruiker.

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

**Let op**: Positions zijn gedeeld tussen alle gebruikers. Alleen admins kunnen deze beheren.

**Indexen:**

-   PRIMARY KEY (`id`)

---

### players

Alle spelers per gebruiker met hun eigenschappen.

| Kolom         | Type            | Nullable | Default        | Beschrijving           |
| ------------- | --------------- | -------- | -------------- | ---------------------- |
| `id`          | bigint unsigned | NO       | AUTO_INCREMENT | Primary key            |
| `user_id`     | bigint unsigned | YES      | NULL           | Eigenaar (gebruiker)   |
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

-   `user_id` → `users(id)` ON DELETE SET NULL
-   `position_id` → `positions(id)`

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`user_id`)
-   INDEX (`position_id`)

---

### opponents

Tegenstanders per gebruiker.

| Kolom        | Type            | Nullable | Default        | Beschrijving             |
| ------------ | --------------- | -------- | -------------- | ------------------------ |
| `id`         | bigint unsigned | NO       | AUTO_INCREMENT | Primary key              |
| `user_id`    | bigint unsigned | YES      | NULL           | Eigenaar (gebruiker)     |
| `name`       | varchar(255)    | NO       |                | Naam van de tegenstander |
| `location`   | varchar(255)    | NO       |                | Plaatsnaam               |
| `logo`       | varchar(255)    | YES      | NULL           | URL naar logo afbeelding |
| `latitude`   | decimal(10,8)   | NO       |                | Breedtegraad             |
| `longitude`  | decimal(11,8)   | NO       |                | Lengtegraad              |
| `created_at` | timestamp       | YES      | NULL           | Aanmaakdatum             |
| `updated_at` | timestamp       | YES      | NULL           | Laatste wijziging        |

**Foreign Keys:**

-   `user_id` → `users(id)` ON DELETE SET NULL

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`user_id`)

---

### formations

Formatie presets (globaal beschikbaar of per gebruiker).

| Kolom              | Type            | Nullable | Default        | Beschrijving                     |
| ------------------ | --------------- | -------- | -------------- | -------------------------------- |
| `id`               | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                      |
| `user_id`          | bigint unsigned | YES      | NULL           | Eigenaar (NULL = globaal)        |
| `total_players`    | int unsigned    | NO       |                | Totaal aantal spelers            |
| `lineup_formation` | varchar(255)    | NO       |                | Formatie string (bijv. "4-3-3")  |
| `is_global`        | boolean         | NO       | false          | Beschikbaar voor alle gebruikers |
| `created_at`       | timestamp       | YES      | NULL           | Aanmaakdatum                     |
| `updated_at`       | timestamp       | YES      | NULL           | Laatste wijziging                |

**Globale formaties:**

-   `is_global = true` - Beschikbaar voor alle gebruikers (bijv. 2-1-2, 3-2-2, 4-3-3)
-   `user_id = NULL` - Geen specifieke eigenaar
-   Alleen admins kunnen globale formaties aanmaken/bewerken

**Gebruiker formaties:**

-   `is_global = false` - Alleen voor specifieke gebruiker
-   `user_id` is ingevuld

**Foreign Keys:**

-   `user_id` → `users(id)` ON DELETE SET NULL

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`user_id`)
-   INDEX (`is_global`)

---

### seasons

Seizoenen per gebruiker.

| Kolom          | Type            | Nullable | Default        | Beschrijving          |
| -------------- | --------------- | -------- | -------------- | --------------------- |
| `id`           | bigint unsigned | NO       | AUTO_INCREMENT | Primary key           |
| `user_id`      | bigint unsigned | YES      | NULL           | Eigenaar (gebruiker)  |
| `formation_id` | bigint unsigned | NO       |                | Gebruikte formatie    |
| `year`         | int             | NO       |                | Jaar (bijv. 2025)     |
| `part`         | varchar(255)    | NO       |                | Deel (bijv. "Najaar") |
| `start_date`   | date            | NO       |                | Startdatum            |
| `end_date`     | date            | NO       |                | Einddatum             |
| `created_at`   | timestamp       | YES      | NULL           | Aanmaakdatum          |
| `updated_at`   | timestamp       | YES      | NULL           | Laatste wijziging     |

**Foreign Keys:**

-   `user_id` → `users(id)` ON DELETE SET NULL
-   `formation_id` → `formations(id)`

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`user_id`)
-   INDEX (`formation_id`)

---

### football_matches

Wedstrijden per gebruiker met resultaten en metadata.

| Kolom            | Type            | Nullable | Default        | Beschrijving            |
| ---------------- | --------------- | -------- | -------------- | ----------------------- |
| `id`             | bigint unsigned | NO       | AUTO_INCREMENT | Primary key             |
| `user_id`        | bigint unsigned | YES      | NULL           | Eigenaar (gebruiker)    |
| `season_id`      | bigint unsigned | NO       |                | Seizoen referentie      |
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

-   `user_id` → `users(id)` ON DELETE SET NULL
-   `season_id` → `seasons(id)`
-   `opponent_id` → `opponents(id)`

**Indexen:**

-   PRIMARY KEY (`id`)
-   INDEX (`user_id`)
-   INDEX (`season_id`)
-   INDEX (`opponent_id`)
-   INDEX (`date`)

---

### football_match_player

Pivot tabel die spelers koppelt aan wedstrijden per kwart (per gebruiker).

| Kolom               | Type             | Nullable | Default | Beschrijving                       |
| ------------------- | ---------------- | -------- | ------- | ---------------------------------- |
| `football_match_id` | bigint unsigned  | NO       |         | Wedstrijd referentie               |
| `user_id`           | bigint unsigned  | YES      | NULL    | Eigenaar (voor data isolatie)      |
| `player_id`         | bigint unsigned  | NO       |         | Speler referentie                  |
| `quarter`           | tinyint unsigned | NO       |         | Kwart (1-4)                        |
| `position_id`       | bigint unsigned  | YES      | NULL    | Positie in dit kwart (NULL = bank) |
| `created_at`        | timestamp        | YES      | NULL    | Aanmaakdatum                       |
| `updated_at`        | timestamp        | YES      | NULL    | Laatste wijziging                  |

**Belangrijke opmerkingen:**

-   `position_id = NULL` betekent dat de speler op de bank zit
-   `position_id` gevuld betekent dat de speler speelt op die positie
-   Elke speler kan meerdere records hebben per wedstrijd (één per kwart)
-   `user_id` zorgt voor data isolatie tussen gebruikers

**Foreign Keys:**

-   `football_match_id` → `football_matches(id)` ON DELETE CASCADE
-   `user_id` → `users(id)` ON DELETE SET NULL
-   `player_id` → `players(id)` ON DELETE CASCADE
-   `position_id` → `positions(id)` ON DELETE SET NULL

**Indexen:**

-   INDEX (`football_match_id`, `player_id`, `quarter`) - Composite voor queries
-   INDEX (`user_id`)
-   INDEX (`player_id`)
-   INDEX (`position_id`)

---

## 🔄 Relaties

### User Ownership (One-to-Many)

Alle hoofdentiteiten behoren toe aan een gebruiker:

-   `users` → `players` (Een gebruiker heeft meerdere spelers)
-   `users` → `opponents` (Een gebruiker heeft meerdere tegenstanders)
-   `users` → `formations` (Een gebruiker heeft meerdere formaties)
-   `users` → `seasons` (Een gebruiker heeft meerdere seizoenen)
-   `users` → `football_matches` (Een gebruiker heeft meerdere wedstrijden)

### Other One-to-Many

-   `positions` → `players` (Een positie heeft meerdere spelers) - **Globaal**
-   `formations` → `seasons` (Een formatie kan in meerdere seizoenen gebruikt worden)
-   `seasons` → `football_matches` (Een seizoen heeft meerdere wedstrijden)
-   `opponents` → `football_matches` (Een tegenstander heeft meerdere wedstrijden)

### Many-to-Many

-   `players` ↔ `football_matches` via `football_match_player`
    -   Extra data: `quarter`, `position_id`, `user_id`

### Global Scope

-   `formations` met `is_global = true` zijn beschikbaar voor alle gebruikers
-   `positions` zijn volledig globaal (geen user_id)

### Polymorphic

Geen polymorphic relaties in de huidige structuur.

## 📈 Query Patterns

### Veelgebruikte queries

**Alle spelers van een gebruiker met keeper statistieken:**

```sql
SELECT p.*, COUNT(fmp.id) as keeper_count
FROM players p
LEFT JOIN football_match_player fmp ON p.id = fmp.player_id AND fmp.position_id = 1
WHERE p.user_id = ?
GROUP BY p.id;
```

**Line-up voor een specifiek kwart (met user check):**

```sql
SELECT p.name, pos.name as position, fmp.quarter
FROM football_match_player fmp
JOIN players p ON fmp.player_id = p.id
LEFT JOIN positions pos ON fmp.position_id = pos.id
WHERE fmp.football_match_id = ?
  AND fmp.quarter = ?
  AND fmp.user_id = ?
ORDER BY fmp.position_id IS NULL, pos.name;
```

**Keepers van laatste wedstrijd (per gebruiker):**

```sql
SELECT DISTINCT p.id, p.name
FROM players p
JOIN football_match_player fmp ON p.id = fmp.player_id
JOIN football_matches fm ON fmp.football_match_id = fm.id
WHERE fmp.position_id = 1
  AND fmp.user_id = ?
ORDER BY fm.date DESC
LIMIT 4;
```

**Globale + eigen formaties ophalen:**

```sql
SELECT * FROM formations
WHERE is_global = 1 OR user_id = ?
ORDER BY is_global DESC, total_players ASC;
```

## 🔒 Data Isolatie & Beveiliging

### Multi-tenancy Strategie

De applicatie gebruikt **user_id scoping** voor data isolatie:

1. **Model Level**: Eloquent Global Scopes filteren automatisch op `user_id`
2. **Policy Level**: Laravel Policies checken ownership voor elke actie
3. **Controller Level**: Automatische user_id toewijzing bij create/update

### Policy Checks

Alle resources hebben policies die controleren:

-   `viewAny`: Alleen eigen data zien (behalve admins)
-   `view`: Ownership check op specifiek item
-   `create`: Actieve gebruiker check
-   `update/delete`: Ownership + actieve status check

**Admin privileges:**

-   Admins kunnen alle data zien en bewerken
-   Admins kunnen globale formaties beheren
-   Admins kunnen gebruikers beheren via `/admin/users`

### Middleware Protection

-   `auth` middleware - Alle routes behalve home
-   `admin` middleware - Admin-only routes (/admin/\*)

## 🚀 Performance Overwegingen

### Indexering

-   Alle foreign keys zijn geïndexeerd (inclusief `user_id`)
-   Composite index op `football_match_player` voor efficiënte line-up queries
-   Date index op `football_matches` voor chronologische queries
-   `is_global` index op `formations` voor snel filteren

### Query Optimalisatie

-   Gebruik van `withCount()` voor aggregatie queries
-   Eager loading voor N+1 query preventie
-   Specifieke select statements waar mogelijk
-   Global scopes voor automatische user_id filtering

### Caching Strategie

-   Model caching voor `positions` (wijzigt zelden, globaal)
-   Query caching voor statistiek overzichten (per user)
-   Page caching voor wedstrijd overzichten

---

## 🔧 Migraties

Migraties zijn te vinden in `/database/migrations/` en worden uitgevoerd in chronologische volgorde:

### Basis Laravel Tabellen

1. `0001_01_01_000000_create_users_table.php` - Gebruikers tabel met authenticatie
2. `0001_01_01_000001_create_cache_table.php` - Cache systeem
3. `0001_01_01_000002_create_jobs_table.php` - Queue jobs systeem

### Core Entiteiten (September 2025)

4. `2025_09_24_121108_create_opponents_table.php` - Tegenstanders tabel
5. `2025_09_24_121127_create_positions_table.php` - Voetbalposities (Keeper, Verdediger, etc.)
6. `2025_09_24_121128_create_players_table.php` - Spelers tabel met position_id
7. `2025_09_24_121228_create_football_matches_table.php` - Wedstrijden tabel
8. `2025_09_24_121404_create_football_match_player_table.php` - Pivot tabel voor line-ups per kwart
9. `2025_09_30_080406_add_weigth_to_players_table.php` - Fysiek niveau (weight) aan spelers

### Seizoenen & Formaties (Oktober 2025)

10. `2025_10_10_135746_create_seasons_table.php` - Seizoenen tabel (jaar, deel, start/eind datum)
11. `2025_10_10_142120_add_season_id_to_football_matches_table.php` - Wedstrijden koppelen aan seizoen
12. `2025_10_10_200000_create_player_season_table.php` - Spelers per seizoen (pivot tabel)
13. `2025_10_17_083540_create_formations_table.php` - Formaties (total_players, lineup_formation)
14. `2025_10_17_085751_add_formations_id_to_seasons_table.php` - Formatie koppelen aan seizoen

### Multi-user Support (30 Oktober 2025)

15. `2025_10_30_151622_add_user_enhancements_to_users_table.php` - Role, team_name, logo, is_active
16. `2025_10_30_151717_add_user_id_to_tables.php` - User_id aan alle tabellen voor data isolatie
17. `2025_10_30_151907_add_is_global_to_formations_table.php` - Globale formaties (beschikbaar voor iedereen)
18. `2025_10_30_153907_add_maps_location_to_users_table.php` - Google Maps locatie voor teams

Voor een fresh installatie:

```bash
php artisan migrate:fresh --seed
```
