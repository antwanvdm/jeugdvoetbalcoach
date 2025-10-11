# ⚽ VVOR Team Manager

Een intelligente teammanagement applicatie voor voetbalverenigingen, gebouwd met Laravel. Deze applicatie automatiseert het maken van line-ups met geavanceerde algoritmes voor eerlijke speeltijdverdeling en strategische teamsamenstelling.

## 🎯 Over dit project

VVOR Team Manager is ontwikkeld voor voetbalverenigingen die hun teammanagement willen professionaliseren. De applicatie neemt het tijdrovende werk van het maken van line-ups uit handen en zorgt voor eerlijke rotatie en optimale teambalans.

### ✨ Kernfunctionaliteiten

**🏆 Intelligente Line-up Generatie**

-   Automatische opstelling voor 4 kwarten per wedstrijd
-   Slimme keeper rotatie (voorkomt herhaling van vorige wedstrijd)
-   Geavanceerde spelersverdeling op basis van fysieke eigenschappen
-   Formatie: 1 Keeper, 2 Verdedigers, 1 Middenvelder, 2 Aanvallers

**⚖️ Eerlijke Speeltijdverdeling**

-   Elke speler speelt exact 2 van de 4 kwarten, behalve de keepers (3 keer)
-   Bank-rotatie in niet-opeenvolgende kwarten (Q1+Q3 vs Q2+Q4)
-   Historische tracking van keeper-beurten
-   Weight-balancing om fysieke clustering te voorkomen

**📊 Uitgebreide Statistieken**

-   Keeper statistieken per speler
-   Speeltijd overzichten per wedstrijd
-   Fysieke verdeling tracking
-   Wedstrijd historie en resultaten

**💼 Beheer & Administratie**

-   Spelersbeheer met posities en fysieke eigenschappen
-   Tegenstander administratie
-   Wedstrijd planning en resultaten
-   Handmatige line-up aanpassingen mogelijk

## 🚀 Aan de slag

### Vereisten

-   PHP 8.2 of hoger
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
```

4. **Database configuratie**

```bash
# Voor SQLite (standaard)
touch database/database.sqlite

# Voor MySQL pas .env aan:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=vvor
# DB_USERNAME=root
# DB_PASSWORD=
```

5. **Database migratie en seeders**

```bash
php artisan migrate
php artisan db:seed
```

6. **Frontend assets**

```bash
npm run build
# Of voor development:
npm run dev
```

7. **Start de server**

```bash
php artisan serve
# Of via herd
```

Ga naar `http://localhost:8000` om de applicatie te gebruiken.

## 🏗️ Architectuur

### Technische Stack

-   **Framework**: Laravel 11
-   **Frontend**: Blade templates met Tailwind CSS
-   **Database**: SQLite/MySQL met Eloquent ORM
-   **Testing**: Pest PHP

### Belangrijkste Components

**LineupGeneratorService** - Kernservice voor line-up generatie

-   Intelligente keeper selectie algoritmes
-   Weight-balancing voor fysieke verdeling
-   Immutable data operations voor betrouwbaarheid
-   Uitgebreide logging en debugging

**Models & Database**

-   `Player` - Spelers met posities en fysieke eigenschappen
-   `FootballMatch` - Wedstrijden met tegenstanders en resultaten
-   `Position` - Keeper, Verdediger, Middenvelder, Aanvaller
-   `Opponent` - Tegenstanders met locatie informatie

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

    - Niet-keepers: alternerend Q1+Q3 vs Q2+Q4 patroon
    - Keepers: 1 bank-kwart (niet hun keeper-kwart)
    - Weight-based sortering voor betere verdeling

3. **Positie Toewijzing**

    - Eerst spelers op hun voorkeursposities
    - Daarna optimalisatie op basis van beschikbaarheid
    - Weight-balancing om clustering te voorkomen

4. **Validatie & Opslag**
    - Controle op formatie (1-2-1-2)
    - Database opslag via pivot tables
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

-   Uitgebreide logging voor troubleshooting
-   Performance metrics en query optimalisatie
-   Debg mode voor development

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

## 📝 Documentatie

-   [LineupGeneratorService](docs/LineupGeneratorService.md) - Uitgebreide service documentatie
-   [Database Schema](docs/database-schema.md) - Database structuur en relaties

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
