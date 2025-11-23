# ⚽ VVOR Team Manager

Een intelligente teammanagement applicatie voor voetbalverenigingen, gebouwd met Laravel. Deze applicatie automatiseert het maken van line-ups met geavanceerde algoritmes voor eerlijke speeltijdverdeling en strategische teamsamenstelling.

## 🎯 Over dit project

VVOR Team Manager is ontwikkeld voor voetbalverenigingen die hun teammanagement willen professionaliseren. De applicatie neemt het tijdrovende werk van het maken van line-ups uit handen en zorgt voor eerlijke rotatie en optimale teambalans. Met de recente multi-team uitbreidingen kun je nu meerdere coaches aan één team koppelen en werken met uitnodigingscodes.

### 👥 Multi-user & Multi-Team Support

De applicatie ondersteunt meerdere teams en gebruikers met een robuust autorisatiesysteem:

-   **Admin rol**: Volledig toegang, beheert globale formaties en gebruikers
-   **Hoofdcoach / Assistent**: Teamrollen via pivot (`team_user`) met autorisatie
-   **Invite codes**: Teams genereren een unieke `invite_code` waarmee andere coaches kunnen joinen
-   **Data isolatie**: Elke gebruiker ziet alleen data van zijn teams
-   **Meerdere teams**: Gebruikers kunnen aan meerdere teams gekoppeld worden en een standaard team instellen
-   **Globale formaties**: Standaard formaties (2-1-2, 3-2-2, 4-3-3) beschikbaar voor alle gebruikers
-   **Policy-based autorisatie**: Laravel Policies voor granulaire toegangscontrole

### ✨ Kernfunctionaliteiten

**🏆 Intelligente Line-up Generatie**

-   Automatische opstelling voor 4 kwarten per wedstrijd
-   Slimme keeperrotatie (voorkomt herhaling van vorige wedstrijd)
-   Geavanceerde spelersverdeling op basis van fysieke eigenschappen
-   Dynamische formatie vanuit het seizoen (bijv. 2-1-2, 3-2-2, 4-3-3) en/of `total_players`

**⚖️ Eerlijke Speeltijdverdeling**

-   Per kwart exact het aantal spelers op het veld dat de formatie vereist (`desiredOnField`), zolang het teamgrootte dat toelaat
-   Bankbehoefte per kwart: `teamSize - desiredOnField`
-   Keepers: ieder 1x bank (niet in hun keeperkwart)
-   Niet-keepers: resterende bankplekken evenwichtig verdeeld over de kwarten (geen hard-coded patronen)
-   Historische tracking van keeperbeurten en weight-balancing om clustering te voorkomen

**📊 Uitgebreide Statistieken**

-   Keeper statistieken per speler
-   Speeltijd overzichten per wedstrijd
-   Fysieke verdeling tracking
-   Wedstrijd historie en resultaten

**💼 Beheer & Administratie**

-   Multi-team structuur met rolverdeling (hoofdcoach/assistent)
-   Gebruikersbeheer met rollen en teamprofielen
-   Spelersbeheer met posities en fysieke eigenschappen
-   Tegenstander administratie
-   Wedstrijd planning en resultaten
-   Handmatige line-up aanpassingen mogelijk
-   Admin dashboard voor gebruikers-, positie- en globale formatiebeheer
-   Uitnodigingscodes voor snelle teamtoetreding

**🔒 Beveiliging & Autorisatie**

-   Laravel Breeze authenticatie
-   Role-based access control (Admin/User)
-   Policy-based autorisatie voor alle resources
-   Data isolatie per gebruiker
-   Actieve/inactieve gebruikers beheer

## 🚀 Aan de slag

### Vereisten

-   PHP 8.4 of hoger
-   Composer
-   Node.js & NPM
-   SQLite/MySQL database

### Installatie

1. **Clone het project**

```bash
git clone https://github.com/antwanvdm/vvor-team-manager.git
cd vvor-team-manager
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

6. **Start de server**

```bash
php artisan serve
# Of via herd
```

Ga naar `http://localhost:8000` om de applicatie te gebruiken.

### Vooringevulde data (seeders)

Bij het seeden worden deze basisgegevens aangemaakt voor een snelle start:

-   **Users** (Admin & 1 default user):
    -   1 Keeper, 2 Verdediger, 3 Middenvelder, 4 Aanvaller
-   **Positions** (met vaste IDs):
    -   1 Keeper, 2 Verdediger, 3 Middenvelder, 4 Aanvaller
-   **Formations** (globale presets):
    -   6 spelers: `2-1-2`
    -   8 spelers: `3-2-2`
    -   11 spelers: `4-3-3`
-   **Opponents**: Set aan tegenstanders met naam, plaats, logo en GPS-coördinaten
-   **Players**: 8 fake spelers

Commands:

```bash
php artisan migrate --seed
# of
php artisan migrate:fresh --seed
```

## 🏗️ Architectuur

### Technische Stack

-   **Framework**: Laravel 11
-   **Authenticatie**: Laravel Breeze
-   **Frontend**: Blade templates met Tailwind CSS (Vite + Tailwind v4)
-   **Database**: SQLite/MySQL met Eloquent ORM
-   **Autorisatie**: Laravel Policies
-   **Testing**: Pest PHP

### Belangrijkste Components

**LineupGeneratorService** - Kernservice voor line-up generatie

-   Intelligente keeper selectie algoritmes
-   Weight-balancing voor fysieke verdeling
-   Immutable data operations voor betrouwbaarheid
-   Uitgebreide logging en debugging

**Models & Database**

-   `Team` - Teams met invite code, logo, location (meerdere coaches)
-   `User` - Gebruikers met rollen en teamprofielen (koppeling via `team_user`)
-   `Player` - Spelers met posities en fysieke eigenschappen (per team)
-   `FootballMatch` - Wedstrijden met tegenstanders en resultaten
-   `Position` - Keeper, Verdediger, Middenvelder, Aanvaller
-   `Opponent` - Tegenstanders met locatie informatie
-   `Formation` - Formaties (globaal of per gebruiker / team)
-   `Season` - Seizoenen gekoppeld aan team en formatie

**Policies & Autorisatie**

-   `PlayerPolicy` - Controleert toegang tot eigen spelers
-   `FormationPolicy` - Admin kan alles, users alleen eigen/globale formaties
-   `SeasonPolicy` - Toegang tot eigen seizoenen
-   `OpponentPolicy` - Toegang tot eigen tegenstanders
-   `FootballMatchPolicy` - Toegang tot eigen wedstrijden
-   `IsAdmin` middleware - Beschermt admin-only routes

### Code Kwaliteit

-   Clean Architecture principes
-   SOLID design patterns
-   Comprehensive testing suite
-   PSR-4 autoloading standaard

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

-   Penalty system voor clustering (>2 gelijk = zwaar gestraft)
-   Distributie optimalisatie over kwarten
-   Kandidaat selectie op basis van balans impact

**🔄 Keeper Rotatie**
Intelligente keeper verdeling die:

-   Laatste wedstrijd keepers excludeert
-   Historische counts balanceert
-   Fair play principes hanteert

**📊 Real-time Monitoring**

-   Uitgebreide logging voor troubleshooting (zet `APP_DEBUG=true`)
-   Performance metrics en query optimalisatie
-   Debug mode voor development

## 🛠️ Development

### Testing

```bash
# Run alle tests
php artisan test

# Met coverage
php artisan test --coverage
```

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

## 🗺️ Roadmap

-   [ ] Cascade on delete als gebruiker profiel verwijderd
-   [x] Multi-team support & koppelen van coaches (pivot + invite codes)
-   [ ] Formatie ook per wedstrijd kunnen aanpassen (is nu enkel per seizoensblok)
-   [ ] Tegenstanders mass import via admin, koppelen vanuit gebruikers
-   [ ] JO13+ support met 11 spelers en twee helften i.p.v. 4 kwarten
-   [ ] Visuele statistieken dashboard (grafieken) voor spelers & keeperrotatie
-   [ ] Export van line-ups naar PDF met verbeterde layout

## 📝 Documentatie

-   [LineupGeneratorService](docs/LineupGeneratorService.md) - Uitgebreide service documentatie
-   [Database Schema](docs/database-schema.md) - Database structuur en relaties
-   Publieke homepage layout & component structuur (zie sectie hieronder)

## 🌐 Publieke Homepage

De publieke homepage is gemoderniseerd met een hero sectie (full-width background image + gradient overlay), een features grid, "Hoe het werkt" stappen, een voorbeeld (screenshot placeholder) en een duidelijke call-to-action. Alle secties zijn opgebouwd met Tailwind utility classes voor snelle aanpasbaarheid.

### Secties

1. Hero: Titel, korte pitch, primaire CTA (Registreren) + secundaire (Inloggen).
2. Features Grid: Kernfunctionaliteiten (line-up generatie, multi-team, seizoenen, statistieken, autorisatie).
3. Hoe het werkt: 4 stappen (Account → Team → Spelers → Wedstrijd & Line-up).
4. Voorbeeld/Screenshot: Placeholder container waar later een echte screenshot / video embed kan komen.
5. CTA Footer: Extra oproep om te starten.

### Aanpasbare onderdelen

-   Achtergrondafbeelding aanpasbaar via inline style of via een class in `app.css`.
-   Iconen kunnen vervangen worden door SVG's in `resources/views/components`.
-   Extra secties kunnen eenvoudig toegevoegd worden als nieuwe `<section>` blokken.

### Toekomstige uitbreidingen

-   Dynamische testimonials (JSON feed / database)
-   Live statistiek preview (keeperrotatie / speeltijd grafiek)
-   Interactie animaties (Framer Motion via React/Vite optioneel)

## 🤝 Bijdragen

Bijdragen zijn welkom! Voor grote wijzigingen, open eerst een issue om te bespreken wat je wilt veranderen.

1. Fork het project
2. Maak een feature branch (`git checkout -b feature/nieuwe-functie`)
3. Commit je wijzigingen (`git commit -am 'Voeg nieuwe functie toe'`)
4. Push naar de branch (`git push origin feature/nieuwe-functie`)
5. Open een Pull Request

## 📄 Licentie

Dit project is gelicenseerd onder de MIT License - zie het [LICENSE](LICENSE) bestand voor details.

---

**Gemaakt met ❤️ voor VVOR en de voetbalgemeenschap**
