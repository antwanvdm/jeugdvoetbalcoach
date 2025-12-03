# ⚽ jeugdvoetbalcoach.nl

Een intelligente teammanagement applicatie voor jeugdvoetbal trainers (JO8-JO12), gebouwd met Laravel. Deze applicatie automatiseert het maken van line-ups met geavanceerde algoritmes voor eerlijke speeltijdverdeling en strategische teamsamenstelling
voor wedstrijden in 4 kwarten.

🌐 **Live op**: [jeugdvoetbalcoach.nl](https://jeugdvoetbalcoach.nl)

## 🎯 Over dit project

jeugdvoetbalcoach.nl is ontwikkeld voor jeugdvoetbal trainers die hun teammanagement willen professionaliseren. Speciaal ontworpen voor JO8 t/m JO12 teams die wedstrijden spelen in 4 kwarten. De applicatie neemt het tijdrovende werk van het maken van
line-ups uit handen en zorgt voor eerlijke rotatie en optimale teambalans. Met de recente multi-team uitbreidingen kun je nu meerdere coaches aan één team koppelen en werken met uitnodigingscodes.

### 👥 Multi-user & Multi-Team Support

De applicatie ondersteunt meerdere teams en gebruikers met een robuust autorisatiesysteem:

- **Admin rol**: Volledig toegang, beheert globale formaties en gebruikers
- **Hoofdcoach / Assistent**: Teamrollen via pivot (`team_user`) met autorisatie
- **Invite codes**: Teams genereren een unieke `invite_code` waarmee andere coaches kunnen joinen
- **Data isolatie**: Elke gebruiker ziet alleen data van zijn teams
- **Meerdere teams**: Gebruikers kunnen aan meerdere teams gekoppeld worden en een standaard team instellen
- **Globale formaties**: Standaard formaties (2-1-2, 3-2-2, 4-3-3) beschikbaar voor alle gebruikers
- **Policy-based autorisatie**: Laravel Policies voor granulaire toegangscontrole

### ✨ Kernfunctionaliteiten

**🏆 Intelligente Line-up Generatie**

- Automatische opstelling voor 4 kwarten per wedstrijd
- Slimme keeperrotatie (voorkomt herhaling van vorige wedstrijd)
- Geavanceerde spelersverdeling op basis van fysieke eigenschappen
- Dynamische formatie vanuit het seizoen (bijv. 2-1-2, 3-2-2, 4-3-3) en/of `total_players`

**⚖️ Eerlijke Speeltijdverdeling**

- Per kwart exact het aantal spelers op het veld dat de formatie vereist (`desiredOnField`), zolang het teamgrootte dat toelaat
- Bankbehoefte per kwart: `teamSize - desiredOnField`
- Keepers: ieder 1x bank (niet in hun keeperkwart)
- Niet-keepers: resterende bankplekken evenwichtig verdeeld over de kwarten (geen hard-coded patronen)
- Historische tracking van keeperbeurten en weight-balancing om clustering te voorkomen

**📊 Uitgebreide Statistieken**

- Keeper statistieken per speler
- Speeltijd overzichten per wedstrijd
- Fysieke verdeling tracking
- Wedstrijd historie en resultaten
- Goals en assists tracking per speler
- Seizoensoverzichten met optionele doelpunten statistieken

**💼 Beheer & Administratie**

- Multi-team structuur met rolverdeling (hoofdcoach/assistent)
- Coach labels voor rolbeschrijvingen binnen teams
- Gebruikersbeheer met rollen en teamprofielen
- Spelersbeheer met posities en fysieke eigenschappen
- Tegenstander administratie
- Wedstrijd planning en resultaten
- Notities per wedstrijd voor extra context
- Handmatige line-up aanpassingen mogelijk
- Admin dashboard voor gebruikers-, positie- en globale formatiebeheer
- Uitnodigingscodes voor snelle teamtoetreding
- Deelbare wedstrijd- en seizoensoverzichten via share tokens

**🔒 Beveiliging & Autorisatie**

- Laravel Breeze authenticatie
- Role-based access control (Admin/User)
- Policy-based autorisatie voor alle resources
- Data isolatie per gebruiker
- Actieve/inactieve gebruikers beheer

## 🚀 Aan de slag

### Vereisten

- PHP 8.4 of hoger
- Composer
- Node.js & NPM
- SQLite/MySQL database

### Installatie

1. **Clone het project**

```bash
git clone https://github.com/antwanvdm/jeugdvoetbalcoach.git
cd jeugdvoetbalcoach
```

2. **Installeer dependencies**

```bash
composer install
npm install
```

3. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

4. **Database migratie en seeders**

```bash
php artisan migrate --seed
```

5. **Frontend assets**

```bash
npm run build
# Of voor development:
npm run dev
```

6. **Genereer responsive afbeeldingen**

```bash
php artisan images:generate-responsive
```

Dit commando genereert geoptimaliseerde WebP varianten (small, medium, large) van alle JPG/PNG afbeeldingen in `resources/images/` en creëert een `image-meta.json` bestand met dimensies. Deze gegenereerde afbeeldingen staan in `.gitignore` om
repository-grootte te beperken, dus **dit commando moet na elke clone/pull gedraaid worden**.

7. **Start de server**

```bash
php artisan serve
# Of via herd
```

Ga naar `http://localhost:8000` om de applicatie te gebruiken.

### Vooringevulde data (seeders)

Bij het seeden worden deze basisgegevens aangemaakt voor een snelle start:

- **Users** (Admin & 1 default user):
    - 1 Keeper, 2 Verdediger, 3 Middenvelder, 4 Aanvaller
- **Positions** (met vaste IDs):
    - 1 Keeper, 2 Verdediger, 3 Middenvelder, 4 Aanvaller
- **Formations** (globale presets):
    - 6 spelers: `2-1-2`
    - 8 spelers: `3-2-2`
    - 11 spelers: `4-3-3`
- **Opponents**: Globale tegenstanders (gedeeld tussen alle teams)
- **Players**: 8 fake spelers

Commands:

```bash
php artisan migrate --seed
# of
php artisan migrate:fresh --seed

# Importeer tegenstanders van Hollandse Velden (met logo's)
php artisan clubs:fetch
```

## 🏗️ Architectuur

### Technische Stack

- **Framework**: Laravel 11
- **Authenticatie**: Laravel Breeze
- **Frontend**: Blade templates met Tailwind CSS (Vite + Tailwind v4)
- **Database**: SQLite/MySQL met Eloquent ORM
- **Autorisatie**: Laravel Policies
- **Testing**: Pest PHP

### Belangrijkste Components

**LineupGeneratorService** - Kernservice voor line-up generatie

- Intelligente keeper selectie algoritmes
- Weight-balancing voor fysieke verdeling
- Immutable data operations voor betrouwbaarheid
- Uitgebreide logging en debugging

**Models & Database**

- `Team` - Teams met invite code en opponent koppeling (meerdere coaches via pivot)
- `User` - Gebruikers met rollen (admin/user), gekoppeld aan teams via `team_user`
- `Player` - Spelers met posities en fysieke eigenschappen (per team)
- `FootballMatch` - Wedstrijden met tegenstanders en resultaten
- `Position` - Keeper, Verdediger, Middenvelder, Aanvaller (globaal)
- `Opponent` - Globale tegenstanders met naam, locatie, logo, coördinaten en kit referentie
- `Formation` - Formaties (globaal of per team)
- `Season` - Seizoenen gekoppeld aan team en formatie

**Policies & Autorisatie**

- `PlayerPolicy` - Controleert toegang tot eigen spelers
- `FormationPolicy` - Admin kan alles, users alleen eigen/globale formaties
- `SeasonPolicy` - Toegang tot eigen seizoenen
- `OpponentPolicy` - Toegang tot eigen tegenstanders
- `FootballMatchPolicy` - Toegang tot eigen wedstrijden
- `IsAdmin` middleware - Beschermt admin-only routes

### Code Kwaliteit

- Clean Architecture principes
- SOLID design patterns
- Comprehensive testing suite
- PSR-4 autoloading standaard

## 📈 Hoe het werkt

### Line-up Generatie Algoritme

1. **Keeper Selectie**

    - Prioriteit voor spelers die vorige wedstrijd NIET hebben gekeept
    - Balanceert historische keeper-ervaring
    - Optimaliseert fysieke diversiteit

2. **Bank Planning**

    - Bepaalt per kwart de bankbehoefte: `teamSize - desiredOnField`
    - Keepers: ieder precies 1x bank (niet in hun keeperkwart)
    - Niet-keepers: resterende bankplekken gelijkmatig verdeeld over de kwarten (geen vast Q1+Q3 of Q2+Q4 patroon)

3. **Positie Toewijzing**

    - Formatiegestuurd: outfield-behoefte komt uit `Season->formation` (`lineup_formation` en/of `total_players`)
    - Eerst spelers op hun voorkeursposities, daarna opvullen met beste kandidaten
    - Weight-balancing om clustering te voorkomen

4. **Validatie & Opslag**
    - Valideert dat per kwart maximaal `desiredOnField` spelers op het veld staan
    - Database opslag via pivot tabel met `quarter` en `position_id`
    - Real-time feedback en logging

### Slimme Features

**🧠 Weight Balancing**
Voorkomt dat 5 spelers met fysiek niveau 1 tegelijk spelen door:

- Penalty system voor clustering (>2 gelijk = zwaar gestraft)
- Distributie optimalisatie over kwarten
- Kandidaat selectie op basis van balans impact

**🔄 Keeper Rotatie**
Intelligente keeper verdeling die:

- Laatste wedstrijd keepers excludeert
- Historische counts balanceert
- Fair play principes hanteert

**📊 Real-time Monitoring**

- Uitgebreide logging voor troubleshooting (zet `APP_DEBUG=true`)
- Performance metrics en query optimalisatie
- Debug mode voor development

## 🛠️ Development

### Testing

#### Test Environment Setup

Tests draaien op een geïsoleerde SQLite database. Maak een `.env.testing` bestand aan (staat in .gitignore):

```env
APP_NAME="Jeugdvoetbalcoach Test"
APP_ENV=testing
APP_KEY=base64:your_test_key_here
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=database/test.sqlite

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

Genereer een test key:

```bash
php artisan key:generate --env=testing
```

Maak de SQLite database aan:

```bash
touch database/test.sqlite
```

#### Tests Uitvoeren

```bash
# Alle tests
php artisan test

# Specifieke test class
php artisan test --filter=LineupGeneratorServiceTest

# Met coverage
php artisan test --coverage

# Specifieke test methode
php artisan test --filter=test_generates_lineup_with_one_keeper
```

#### Test Suite Overview

**LineupGeneratorServiceTest** (22 tests):

- Keeper scenarios: 0, 1, 2, 3, 4 keepers
- Bench fairness: historische tracking over 3-10 wedstrijden
- Formatie validatie: 2-1-2, 3-2-2
- Variabele beschikbaarheid: spelers wisselend beschikbaar
- Positie voorkeuren en weight balancing
- Edge cases: kleine teams, verschillende formaties

Zie `docs/LineupGeneratorService.md` voor gedetailleerde test documentatie.

### Code Style

```bash
# PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Static Analysis
./vendor/bin/phpstan analyse
```

### Database

```bash
# Fresh migrate met test data
php artisan migrate:fresh --seed

# Nieuwe migration
php artisan make:migration create_example_table
```

## 📈 Hoe het werkt

### Line-up Generatie Algoritme

1. **Keeper Selectie**

    - Prioriteit voor spelers die vorige wedstrijd NIET hebben gekeept
    - Balanceert historische keeper-ervaring
    - Optimaliseert fysieke diversiteit

2. **Bank Planning**

    - Bepaalt per kwart de bankbehoefte: `teamSize - desiredOnField`
    - Keepers: ieder precies 1x bank (niet in hun keeperkwart)
    - Niet-keepers: resterende bankplekken gelijkmatig verdeeld over de kwarten (geen vast Q1+Q3 of Q2+Q4 patroon)

3. **Positie Toewijzing**

    - Formatiegestuurd: outfield-behoefte komt uit `Season->formation` (`lineup_formation` en/of `total_players`)
    - Eerst spelers op hun voorkeursposities, daarna opvullen met beste kandidaten
    - Weight-balancing om clustering te voorkomen

4. **Validatie & Opslag**
    - Valideert dat per kwart maximaal `desiredOnField` spelers op het veld staan
    - Database opslag via pivot tabel met `quarter` en `position_id`
    - Real-time feedback en logging

### Slimme Features

**🧠 Weight Balancing**
Voorkomt dat 5 spelers met fysiek niveau 1 tegelijk spelen door:

- Penalty system voor clustering (>2 gelijk = zwaar gestraft)
- Distributie optimalisatie over kwarten
- Kandidaat selectie op basis van balans impact

**🔄 Keeper Rotatie**
Intelligente keeper verdeling die:

- Laatste wedstrijd keepers excludeert
- Historische counts balanceert
- Fair play principes hanteert

**📊 Real-time Monitoring**

- Uitgebreide logging voor troubleshooting (zet `APP_DEBUG=true`)
- Performance metrics en query optimalisatie
- Debug mode voor development

## 📝 Documentatie

- [LineupGeneratorService](docs/LineupGeneratorService.md) - Uitgebreide service documentatie
- [Database Schema](docs/database-schema.md) - Database structuur en relaties

## 🤝 Bijdragen

Bijdragen zijn welkom! Voor grote wijzigingen, open eerst een issue.

1. Fork het project
2. Maak een feature branch (`git checkout -b feature/nieuwe-functie`)
3. Commit je wijzigingen (`git commit -am 'Voeg nieuwe functie toe'`)
4. Push naar de branch (`git push origin feature/nieuwe-functie`)
5. Open een Pull Request

## 🗺️ Roadmap

### ✅ Gerealiseerd

-   [x] Multi-team support & koppelen van coaches (pivot + invite codes)
-   [x] Formatie ook per wedstrijd kunnen aanpassen (is nu enkel per seizoensblok)
-   [x] Tegenstanders mass import via admin, koppelen vanuit gebruikers
-   [x] Deelbare view met unieke link voor niet ingelogde gebruikers van losse wedstrijden
-   [x] Aanmaken wedstrijd moet optie geven om afwezige spelers aan te geven (IPV achteraf alles te moeten aanpassen)
-   [x] Spelers aanmaken moet in 1 keer simpel kunnen, ook moeten die gewoon gelijk aan actieve seizoen gekoppeld worden
-   [x] Goals en assists bijhouden van spelers per wedstrijd
-   [x] Seizoenen ook kunnen delen met overzicht van wedstrijden en (optioneel) statistieken
-   [x] Labels aan coaches kunnen geven binnen een team (bijv. "Hoofdtrainer", "Keeperstrainer")
-   [x] Notities toevoegen aan wedstrijden voor extra context
-   [x] Share tokens voor wedstrijden en seizoenen voor publieke toegang
-   [x] Opruimen van data als gebruiker account verwijderd
-   [x] Statistieken voor admin over aantallen aan data in de DB
-   [x] Unit tests voor line-up met meerdere scenario's maken en uitvoeren

### 🔜 Toekomstige Features

-   [ ] Webservice maken zodat andere developers gemakkelijk voetbalclub data kunnen inladen
-   [ ] JO13+ support met 11 spelers en twee helften i.p.v. 4 kwarten

## 📄 Licentie

Dit project is gelicenseerd onder de MIT License - zie het [LICENSE](LICENSE) bestand voor details.

---

**Ontwikkeld door [Antwan van der Mooren](https://github.com/antwanvdm) met ❤️ voor VVOR en de voetbalgemeenschap**
