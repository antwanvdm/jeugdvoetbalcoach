# Database Schema

Dit document beschrijft de database structuur van de Jeugdvoetbalcoach.nl applicatie.

## Overzicht

De database ondersteunt een **multi-team architectuur** waarbij meerdere coaches aan één team kunnen werken. Admins hebben volledige toegang, reguliere gebruikers zien alleen data van hun teams.

De database bestaat uit de volgende hoofdtabellen:

- **teams** - Teams gekoppeld aan opponent (voor team info)
- **users** - Gebruikers met rollen (admin/user)
- **team_user** - Pivot tabel die users aan teams koppelt met rol (hoofdcoach/assistent)
- **players** - Spelers per team
- **positions** - Voetbalposities (globaal, gedeeld)
- **opponents** - Globale tegenstanders (gedeeld tussen alle teams)
- **formations** - Formatie presets (globaal of per team)
- **seasons** - Seizoenen per team
- **football_matches** - Wedstrijden per team
- **football_match_player** - Pivot tabel voor line-ups

## 📊 Entity Relationship Diagram

```
┌─────────────────┐              ┌─────────────────┐
│     users       │◄────────────►│   team_user     │
├─────────────────┤   M      M   ├─────────────────┤
│ id (PK)         │              │ team_id (PK,FK) │
│ name            │              │ user_id (PK,FK) │
│ email           │              │ role            │
│ password        │              │ is_default      │
│ role            │              │ joined_at       │
│ is_active       │              │ label           │
└─────────────────┘              └─────────────────┘
                                          │
                                          │ M
                                          ▼
                                 ┌─────────────────┐
                                 │     teams       │
                                 ├─────────────────┤
                                 │ id (PK)         │
                                 │ opponent_id (FK)│───┐
                                 │ invite_code     │   │
                                 └─────────────────┘   │
                                          │            │
                                          │ (owns)     │
                   ┌──────────────────────┼────────┐   │
                   │                      │        │   │
                   ▼                      ▼        ▼   │
         ┌─────────────────┐    ┌─────────────┐ ┌───────────┐
         │    players      │    │ formations  │ │  seasons  │
         ├─────────────────┤    ├─────────────┤ ├───────────┤
         │ id (PK)         │    │ id (PK)     │ │ id (PK)   │
         │ team_id (FK)    │    │ team_id(FK) │ │ team_id   │
         │ name            │    │ total_play. │ │ form._id  │
         │ position_id (FK)│    │ lineup_form │ │ year/part │
         │ weight          │    │ is_global   │ │ dates     │
         └─────────────────┘    └─────────────┘ │track_goals│
                   │                             │share_token│
  ┌────────────────┼──────────────┐              └───────────┘
  │                │              │                   │
  │                │              │                   │
  │            ┌───┼──────────────┼───────────────────┘
  │            │   │              │
┌───────────┐  │   │    ┌─────────────────┐
│ positions │  │   │    │ opponents       │◄──────────┘
├───────────┤  │   │    ├─────────────────┤ (globaal)
│ id (PK)   │  │   │    │ id (PK)         │
│ name      │  │   │    │ name            │
└───────────┘  │   │    │ location        │
      │        │   │    │ logo            │
      └────┬───┘   │    │ latitude        │
           │       │    │ longitude       │
           │       │    │ kit_reference   │
           │       │    └─────────────────┘
           │       │             │
           │       │             │
           ▼       │             │
  ┌─────────────────────────────┐│
  │   football_matches          ││
  ├─────────────────────────────┤│
  │ id (PK)                     ││
  │ team_id (FK)                ││
  │ share_token                 ││
  │ season_id (FK)              ││
  │ opponent_id (FK)            │◄┘
  │ home (boolean)              │
  │ goals_scored/conceded       │
  │ date, notes                 │
  └─────────────────────────────┘
       │                    │
       │ (line-up)          │ (goals)
       ▼                    ▼
  ┌──────────────────┐  ┌─────────────────┐
  │football_match_   │  │  match_goals    │
  │     player       │  ├─────────────────┤
  ├──────────────────┤  │ id (PK)         │
  │match_id (FK)     │  │ match_id (FK)   │
  │player_id (FK)    │  │ player_id (FK)  │
  │quarter (1-4)     │  │ assist_pl_id(FK)│
  │position_id (FK)  │  │ minute, subtype │
  └──────────────────┘  │ notes           │
           │            └─────────────────┘
           └──────────────────┘
```

## 📋 Tabellen

### teams

Teams vormen de centrale entiteit voor samenwerking tussen meerdere coaches.

| Kolom           | Type            | Nullable | Default        | Beschrijving                  |
|-----------------|-----------------|----------|----------------|-------------------------------|
| `id`            | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                   |
| `name`          | varchar(255)    | NO       |                | Teamnaam                      |
| `logo`          | varchar(255)    | YES      | NULL           | Logo URL                      |
| `maps_location` | varchar(255)    | YES      | NULL           | Locatie / adres / coördinaten |
| `invite_code`   | varchar(64)     | YES      | NULL           | Unieke code om team te joinen |
| `created_at`    | timestamp       | YES      | NULL           | Aanmaakdatum                  |
| `updated_at`    | timestamp       | YES      | NULL           | Laatste wijziging             |

**Invite Code**: Wordt gegenereerd per team en kan opnieuw aangemaakt worden. Maakt het mogelijk dat andere coaches via een publieke route (`/teams/join/{inviteCode}`) deelnemen.

**Relaties**:

- Heeft meerdere `players`, `seasons`, `opponents`, `football_matches`, `formations`.
- Heeft meerdere `users` via pivot `team_user`.

### team_user (pivot)

Koppelt gebruikers aan teams met rol, standaardstatus, join datum en label.

| Kolom        | Type            | Nullable | Default           | Beschrijving                                       |
|--------------|-----------------|----------|-------------------|----------------------------------------------------|
| `team_id`    | bigint unsigned | NO       |                   | Verwijst naar team                                 |
| `user_id`    | bigint unsigned | NO       |                   | Verwijst naar gebruiker                            |
| `role`       | tinyint         | NO       |                   | 1 = hoofdcoach, 2 = assistent                      |
| `is_default` | boolean         | NO       | false             | Of dit het standaard team is                       |
| `joined_at`  | timestamp       | NO       | CURRENT_TIMESTAMP | Tijdstip van toetreding                            |
| `label`      | varchar(180)    | YES      | NULL              | Optionele rolbeschrijving (bijv. "Keeperstrainer") |

**Primary Key**: Composite (`team_id`, `user_id`).

**Indexen**: Impliciet via primary key; extra indexen kunnen later worden toegevoegd voor filtering op rol / default status.

**Autorisatie**: Policies gebruiken de pivot-rol om toegangsrechten binnen een team te bepalen (bijv. hoofdcoach kan formatie wijzigen, assistent read-only op bepaalde onderdelen - afhankelijk van implementatie).

### users

Gebruikers van het systeem met rollen.

| Kolom               | Type            | Nullable | Default        | Beschrijving          |
|---------------------|-----------------|----------|----------------|-----------------------|
| `id`                | bigint unsigned | NO       | AUTO_INCREMENT | Primary key           |
| `name`              | varchar(255)    | NO       |                | Gebruikersnaam        |
| `email`             | varchar(255)    | NO       |                | E-mailadres (uniek)   |
| `email_verified_at` | timestamp       | YES      | NULL           | Verificatie timestamp |
| `password`          | varchar(255)    | NO       |                | Gehashed wachtwoord   |
| `role`              | tinyint         | NO       | 2              | Rol (1=admin, 2=user) |
| `is_active`         | boolean         | NO       | true           | Actieve status        |
| `remember_token`    | varchar(100)    | YES      | NULL           | Remember me token     |
| `created_at`        | timestamp       | YES      | NULL           | Aanmaakdatum          |
| `updated_at`        | timestamp       | YES      | NULL           | Laatste wijziging     |

**Role systeem:**

- `1` = Admin - Volledige toegang, kan gebruikers beheren
- `2` = User - Standaard gebruiker, lid van een of meerdere teams

**Relaties:**

- Heeft meerdere `teams` via pivot `team_user`

**Indexen:**

- PRIMARY KEY (`id`)
- UNIQUE KEY (`email`)

---

### positions

Voetbalposities die spelers kunnen spelen. **Globale tabel**, niet per gebruiker.

| Kolom        | Type            | Nullable | Default        | Beschrijving        |
|--------------|-----------------|----------|----------------|---------------------|
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

- PRIMARY KEY (`id`)

---

### players

Alle spelers per team met hun eigenschappen.

| Kolom         | Type            | Nullable | Default        | Beschrijving               |
|---------------|-----------------|----------|----------------|----------------------------|
| `id`          | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                |
| `team_id`     | bigint unsigned | YES      | NULL           | Team waar speler bij hoort |
| `name`        | varchar(255)    | NO       |                | Naam van de speler         |
| `position_id` | bigint unsigned | NO       |                | Favoriete/hoofdpositie     |
| `weight`      | tinyint         | NO       | 1              | Fysiek niveau (1-2)        |
| `created_at`  | timestamp       | YES      | NULL           | Aanmaakdatum               |
| `updated_at`  | timestamp       | YES      | NULL           | Laatste wijziging          |

**Weight systeem:**

- `1` = Normale speler
- `2` = Sterkere speler

**Foreign Keys:**

- `team_id` → `teams(id)` ON DELETE SET NULL
- `position_id` → `positions(id)`

**Indexen:**

- PRIMARY KEY (`id`)
- INDEX (`team_id`)
- INDEX (`position_id`)

---

### opponents

Globale tegenstanders (gedeeld tussen alle teams).

| Kolom           | Type            | Nullable | Default        | Beschrijving                          |
|-----------------|-----------------|----------|----------------|---------------------------------------|
| `id`            | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                           |
| `name`          | varchar(255)    | NO       |                | Naam van de tegenstander/vereniging   |
| `location`      | varchar(255)    | NO       |                | Plaatsnaam                            |
| `logo`          | varchar(255)    | YES      | NULL           | URL naar logo afbeelding              |
| `latitude`      | decimal(10,8)   | NO       | 0.0            | Breedtegraad                          |
| `longitude`     | decimal(11,8)   | NO       | 0.0            | Lengtegraad                           |
| `kit_reference` | varchar(255)    | YES      | NULL           | Referentie naar tenue (uit clubs.csv) |
| `created_at`    | timestamp       | YES      | NULL           | Aanmaakdatum                          |
| `updated_at`    | timestamp       | YES      | NULL           | Laatste wijziging                     |

**Belangrijke opmerkingen:**

- Opponents zijn **globaal** - niet gekoppeld aan specifieke gebruikers of teams
- Logo's kunnen automatisch worden opgehaald via `php artisan clubs:fetch` command
- Teams kunnen een opponent selecteren om team info (naam, logo, locatie) over te nemen

**Indexen:**

- PRIMARY KEY (`id`)

---

### formations

Formatie presets (globaal beschikbaar of per team).

| Kolom              | Type            | Nullable | Default        | Beschrijving                    |
|--------------------|-----------------|----------|----------------|---------------------------------|
| `id`               | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                     |
| `team_id`          | bigint unsigned | YES      | NULL           | Team (NULL = globaal)           |
| `total_players`    | int unsigned    | NO       |                | Totaal aantal spelers           |
| `lineup_formation` | varchar(255)    | NO       |                | Formatie string (bijv. "4-3-3") |
| `is_global`        | boolean         | NO       | false          | Beschikbaar voor alle teams     |
| `created_at`       | timestamp       | YES      | NULL           | Aanmaakdatum                    |
| `updated_at`       | timestamp       | YES      | NULL           | Laatste wijziging               |

**Globale formaties:**

- `is_global = true` - Beschikbaar voor alle teams (bijv. 2-1-2, 3-2-2, 4-3-3)
- `team_id = NULL` - Geen specifieke eigenaar
- Alleen admins kunnen globale formaties aanmaken/bewerken

**Team formaties:**

- `is_global = false` - Alleen voor specifiek team
- `team_id` is ingevuld

**Foreign Keys:**

- `team_id` → `teams(id)` ON DELETE SET NULL

**Indexen:**

- PRIMARY KEY (`id`)
- INDEX (`team_id`)
- INDEX (`is_global`)

---

### seasons

Seizoenen per team.

| Kolom          | Type            | Nullable | Default        | Beschrijving                               |
|----------------|-----------------|----------|----------------|--------------------------------------------|
| `id`           | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                                |
| `team_id`      | bigint unsigned | YES      | NULL           | Team                                       |
| `formation_id` | bigint unsigned | NO       |                | Gebruikte formatie                         |
| `year`         | int             | NO       |                | Jaar (bijv. 2025)                          |
| `part`         | varchar(255)    | NO       |                | Fase (bijv. "1")                           |
| `start_date`   | date            | NO       |                | Startdatum                                 |
| `end_date`     | date            | NO       |                | Einddatum                                  |
| `track_goals`  | boolean         | NO       | false          | Of doelpunten bijgehouden worden           |
| `share_token`  | varchar(64)     | YES      | NULL           | Unieke token voor publieke toegang (uniek) |
| `created_at`   | timestamp       | YES      | NULL           | Aanmaakdatum                               |
| `updated_at`   | timestamp       | YES      | NULL           | Laatste wijziging                          |

**Foreign Keys:**

- `team_id` → `teams(id)` ON DELETE SET NULL
- `formation_id` → `formations(id)`

**Indexen:**

- PRIMARY KEY (`id`)
- INDEX (`team_id`)
- INDEX (`formation_id`)

---

### football_matches

Wedstrijden per team met resultaten en metadata.

| Kolom            | Type            | Nullable | Default        | Beschrijving                               |
|------------------|-----------------|----------|----------------|--------------------------------------------|
| `id`             | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                                |
| `team_id`        | bigint unsigned | YES      | NULL           | Team                                       |
| `share_token`    | varchar(32)     | YES      | NULL           | Unieke token voor publieke toegang (uniek) |
| `season_id`      | bigint unsigned | NO       |                | Seizoen referentie                         |
| `opponent_id`    | bigint unsigned | NO       |                | Tegenstander                               |
| `home`           | tinyint(1)      | NO       |                | Thuis (1) of uit (0)                       |
| `goals_scored`   | int unsigned    | YES      | NULL           | Doelpunten gescoord                        |
| `goals_conceded` | int unsigned    | YES      | NULL           | Doelpunten tegengekregen                   |
| `date`           | datetime        | NO       |                | Wedstrijddatum en tijd                     |
| `notes`          | text            | YES      | NULL           | Notities/aantekeningen bij wedstrijd       |
| `created_at`     | timestamp       | YES      | NULL           | Aanmaakdatum                               |
| `updated_at`     | timestamp       | YES      | NULL           | Laatste wijziging                          |

**Computed properties:**

- `result` - Berekend: 'W' (winst), 'L' (verlies), 'D' (gelijk), 'O' (open)

**Foreign Keys:**

- `team_id` → `teams(id)` ON DELETE SET NULL
- `season_id` → `seasons(id)`
- `opponent_id` → `opponents(id)`

**Indexen:**

- PRIMARY KEY (`id`)
- INDEX (`team_id`)
- INDEX (`season_id`)
- INDEX (`opponent_id`)
- INDEX (`date`)

---

### football_match_player

Pivot tabel die spelers koppelt aan wedstrijden per kwart.

| Kolom               | Type             | Nullable | Default | Beschrijving                       |
|---------------------|------------------|----------|---------|------------------------------------|
| `football_match_id` | bigint unsigned  | NO       |         | Wedstrijd referentie               |
| `player_id`         | bigint unsigned  | NO       |         | Speler referentie                  |
| `quarter`           | tinyint unsigned | NO       |         | Kwart (1-4)                        |
| `position_id`       | bigint unsigned  | YES      | NULL    | Positie in dit kwart (NULL = bank) |
| `created_at`        | timestamp        | YES      | NULL    | Aanmaakdatum                       |
| `updated_at`        | timestamp        | YES      | NULL    | Laatste wijziging                  |

**Belangrijke opmerkingen:**

- `position_id = NULL` betekent dat de speler op de bank zit
- `position_id` gevuld betekent dat de speler speelt op die positie
- Elke speler kan meerdere records hebben per wedstrijd (één per kwart)

**Foreign Keys:**

- `football_match_id` → `football_matches(id)` ON DELETE CASCADE
- `player_id` → `players(id)` ON DELETE CASCADE
- `position_id` → `positions(id)` ON DELETE SET NULL

**Indexen:**

- INDEX (`football_match_id`, `player_id`, `quarter`) - Composite voor queries
- INDEX (`player_id`)
- INDEX (`position_id`)

---

### match_goals

Gescoorde doelpunten per wedstrijd met speler en assist informatie.

| Kolom               | Type            | Nullable | Default        | Beschrijving                  |
|---------------------|-----------------|----------|----------------|-------------------------------|
| `id`                | bigint unsigned | NO       | AUTO_INCREMENT | Primary key                   |
| `football_match_id` | bigint unsigned | NO       |                | Wedstrijd referentie          |
| `player_id`         | bigint unsigned | YES      | NULL           | Speler die scoorde            |
| `assist_player_id`  | bigint unsigned | YES      | NULL           | Speler die assist gaf         |
| `minute`            | int             | YES      | NULL           | Minuut waarin gescoord werd   |
| `subtype`           | varchar(255)    | YES      | NULL           | Type doelpunt (bijv. penalty) |
| `notes`             | text            | YES      | NULL           | Extra notities bij doelpunt   |
| `created_at`        | timestamp       | YES      | NULL           | Aanmaakdatum                  |
| `updated_at`        | timestamp       | YES      | NULL           | Laatste wijziging             |

**Belangrijke opmerkingen:**

- `player_id` kan NULL zijn voor eigen doelpunten van tegenstander
- `assist_player_id` is optioneel (niet elk doelpunt heeft een assist)
- `minute` is optioneel maar helpt bij het chronologisch ordenen van doelpunten
- `subtype` kan gebruikt worden voor speciale doelpunten (penalty, vrije trap, etc.)

**Foreign Keys:**

- `football_match_id` → `football_matches(id)` ON DELETE CASCADE
- `player_id` → `players(id)` ON DELETE CASCADE
- `assist_player_id` → `players(id)` ON DELETE SET NULL

**Indexen:**

- PRIMARY KEY (`id`)
- INDEX (`football_match_id`)
- INDEX (`player_id`)
- INDEX (`assist_player_id`)

---

## 🔄 Relaties

### Team Ownership (One-to-Many)

Alle hoofdentiteiten behoren toe aan een team:

- `teams` → `players` (Een team heeft meerdere spelers)
- `teams` → `formations` (Een team heeft meerdere formaties)
- `teams` → `seasons` (Een team heeft meerdere seizoenen)
- `teams` → `football_matches` (Een team heeft meerdere wedstrijden)

### User-Team Relationship (Many-to-Many)

- `users` ↔ `teams` via `team_user` pivot
    - Extra data: `role` (hoofdcoach/assistent), `is_default`, `joined_at`, `label`
    - Een gebruiker kan aan meerdere teams gekoppeld zijn
    - Een team kan meerdere coaches hebben

### Other One-to-Many

- `opponents` → `teams` (Een opponent kan door meerdere teams gebruikt worden voor team info)
- `positions` → `players` (Een positie heeft meerdere spelers) - **Globaal**
- `formations` → `seasons` (Een formatie kan in meerdere seizoenen gebruikt worden)
- `seasons` → `football_matches` (Een seizoen heeft meerdere wedstrijden)
- `opponents` → `football_matches` (Een opponent speelt in meerdere wedstrijden)
- `football_matches` → `match_goals` (Een wedstrijd heeft meerdere doelpunten)
- `players` → `match_goals` (Een speler kan meerdere doelpunten scoren)
- `players` → `match_goals` as assist (Een speler kan meerdere assists geven)

### Many-to-Many

- `players` ↔ `football_matches` via `football_match_player`
    - Extra data: `quarter`, `position_id`

### Global Resources

- `opponents` zijn volledig globaal (geen team_id of user_id) - gedeeld tussen alle teams
- `formations` met `is_global = true` zijn beschikbaar voor alle teams
- `positions` zijn volledig globaal (geen team_id)

### Polymorphic

Geen polymorphic relaties in de huidige structuur.

## 📈 Query Patterns

### Veelgebruikte queries

**Alle spelers van een team met keeper statistieken:**

```sql
SELECT p.*, COUNT(fmp.id) as keeper_count
FROM players p
         LEFT JOIN football_match_player fmp ON p.id = fmp.player_id AND fmp.position_id = 1
WHERE p.team_id = ?
GROUP BY p.id;
```

**Line-up voor een specifiek kwart:**

```sql
SELECT p.name, pos.name as position, fmp.quarter
FROM football_match_player fmp
    JOIN players p
ON fmp.player_id = p.id
    LEFT JOIN positions pos ON fmp.position_id = pos.id
WHERE fmp.football_match_id = ?
  AND fmp.quarter = ?
ORDER BY fmp.position_id IS NULL, pos.name;
```

**Keepers van laatste wedstrijd (per team):**

```sql
SELECT DISTINCT p.id, p.name
FROM players p
         JOIN football_match_player fmp ON p.id = fmp.player_id
         JOIN football_matches fm ON fmp.football_match_id = fm.id
WHERE fmp.position_id = 1
  AND fm.team_id = ?
ORDER BY fm.date DESC LIMIT 4;
```

**Globale + team formaties ophalen:**

```sql
SELECT *
FROM formations
WHERE is_global = 1
   OR team_id = ?
ORDER BY is_global DESC, total_players ASC;
```

**Teams van een gebruiker:**

```sql
SELECT t.*, tu.role, tu.is_default, o.name as opponent_name, o.logo
FROM teams t
         JOIN team_user tu ON t.id = tu.team_id
         LEFT JOIN opponents o ON t.opponent_id = o.id
WHERE tu.user_id = ?
ORDER BY tu.is_default DESC, tu.joined_at ASC;
```

## 🔒 Data Isolatie & Beveiliging

### Multi-tenancy Strategie

De applicatie gebruikt **team_id scoping** voor data isolatie:

1. **Model Level**: Eloquent Global Scopes filteren automatisch op `team_id`
2. **Policy Level**: Laravel Policies checken team membership via `team_user` pivot
3. **Controller Level**: Automatische team_id toewijzing bij create/update
4. **Team Context**: Gebruikers selecteren hun actieve team, data wordt gefilterd op basis daarvan

### Policy Checks

Alle resources hebben policies die controleren:

- `viewAny`: Alleen data van eigen teams zien (behalve admins)
- `view`: Team membership check via `team_user` pivot
- `create`: Team membership + actieve status check
- `update/delete`: Team membership + rol check (hoofdcoach heeft meer rechten)

**Admin privileges:**

- Admins kunnen alle data zien en bewerken
- Admins kunnen globale formaties beheren
- Admins kunnen gebruikers beheren via `/admin/users`
- Admins kunnen globale opponents beheren

**Team Roles:**

- `1` = Hoofdcoach - Kan team settings wijzigen, spelers beheren, line-ups maken
- `2` = Assistent - Kan data bekijken en mogelijk line-ups maken (afhankelijk van implementatie)

### Middleware Protection

- `auth` middleware - Alle routes behalve home
- `admin` middleware - Admin-only routes (/admin/\*)
- Team context middleware - Zorgt dat gebruiker een geldig team geselecteerd heeft

## 🚀 Performance Overwegingen

### Indexering

- Alle foreign keys zijn geïndexeerd (inclusief `team_id`)
- Composite primary key op `team_user` (`team_id`, `user_id`)
- Composite index op `football_match_player` voor efficiënte line-up queries
- Date index op `football_matches` voor chronologische queries
- `is_global` index op `formations` voor snel filteren
- `invite_code` unique index op `teams` voor join functionaliteit
- `share_token` unique indexes op `football_matches` en `seasons` voor publieke toegang
- Indexes op `match_goals` voor efficiënte statistiek queries (player_id, assist_player_id)

### Query Optimalisatie

- Gebruik van `withCount()` voor aggregatie queries
- Eager loading voor N+1 query preventie (bijv. `team.opponent`)
- Specifieke select statements waar mogelijk
- Global scopes voor automatische team_id filtering
- Caching van team membership checks

### Caching Strategie

- Model caching voor `positions` en `opponents` (wijzigt zelden, globaal)
- Query caching voor statistiek overzichten (per team)
- Page caching voor wedstrijd overzichten
- Session caching voor actieve team selectie

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

### Multi-team Support (13 November 2025)

19. `2025_11_13_100000_create_teams_table.php` - Teams tabel met name, logo, maps_location
20. `2025_11_13_100001_create_team_user_table.php` - Pivot tabel voor user-team relatie met rol
21. `2025_11_13_151717_add_team_id_to_tables.php` - Team_id aan players, seasons, opponents, matches, formations
22. `2025_11_13_153328_remove_name_logo_from_users_table.php` - Verwijder team_name, logo, maps_location van users
23. `2025_11_13_153433_add_invite_code_to_teams_table.php` - Invite code voor team joins

### Opponent Updates (23 November 2025)

24. `2025_11_23_112806_update_opponents_table.php` - Kit_reference toevoegen, user_id en team_id verwijderen (globaal)
25. `2025_11_23_150000_add_opponent_id_to_teams_table.php` - Opponent koppeling aan teams, verwijder name/logo/maps_location

### Share & Match Features (25-29 November 2025)

26. `2025_11_25_130616_add_share_token_to_football_matches_table.php` - Share token voor publieke wedstrijd toegang
27. `2025_11_29_000000_add_label_to_team_user_table.php` - Label voor coach rolbeschrijving binnen team
28. `2025_11_29_100000_add_track_goals_to_seasons_table.php` - Track_goals boolean voor seizoenen
29. `2025_11_29_100001_add_notes_and_share_token_to_football_matches_table.php` - Notities toevoegen aan wedstrijden
30. `2025_11_29_100002_add_share_token_to_seasons_table.php` - Share token voor publieke seizoen toegang
31. `2025_11_29_100003_create_match_goals_table.php` - Goals en assists tracking per wedstrijd

Voor een fresh installatie:

```bash
php artisan migrate:fresh --seed

# Optioneel: importeer tegenstanders met logo's
php artisan clubs:fetch
```
