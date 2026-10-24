<x-mail::message>
# Beste {{ $userName }},

Het voetbalseizoen is weer volop bezig. Nieuwe teams, wisselende opkomsten en iedere week opnieuw de uitdaging om iedereen zo eerlijk mogelijk te laten spelen. Daarom zijn er weer een paar verbeteringen toegevoegd aan jeugdvoetbalcoach.nl.

### Nieuw: meer wissels per kwart

Je kunt vanaf nu per seizoen instellen hoeveel wisselmomenten (standaard = 1) je per kwart wilt gebruiken. Handig wanneer je met een grotere groep speelt, of wanneer je iedereen nog wat vaker een kans wilt geven.

Bij het aanmaken of bewerken van een seizoen kies je eenvoudig het gewenste aantal wissels. Per wedstrijd kun je die instelling daarna nog aanpassen als dat een keer beter uitkomt. De opstelling houdt vervolgens rekening met die extra wisselmomenten.

<x-mail::button :url="'https://jeugdvoetbalcoach.nl/seasons'">
    Beheer mijn seizoenen
</x-mail::button>

### Wijzigingen in je team? Geen probleem

Een team blijft natuurlijk niet altijd hetzelfde. Stopt er een speler of komt er iemand bij? Dat kun je netjes verwerken zonder dat je hele seizoen opnieuw hoeft te beginnen.

- **Een speler stopt of gaat naar een ander team:** koppel de speler los van het huidige seizoen. De speler blijft in je spelersoverzicht staan voor je historische data, maar wordt niet meer meegenomen in nieuwe opstellingen voor dit seizoen.
- **Een nieuwe speler erbij:** voeg de speler toe aan je team en koppel hem of haar daarna aan het huidige seizoen. Vanaf dat moment doet de speler gewoon mee in de opstellingen.

Zo blijft je seizoenshistorie kloppen en werk je steeds met de spelers die op dat moment echt bij je team horen.

<x-mail::button :url="'https://jeugdvoetbalcoach.nl/players'">
    Beheer mijn spelers
</x-mail::button>

### Kort offline voor een belangrijke veiligheidsupdate

Misschien heb je gemerkt dat jeugdvoetbalcoach.nl deze week tijdelijk niet bereikbaar was. Dat kwam door een kritieke beveiligingsupdate voor PHP, de techniek waarop het platform draait. Veel Nederlandse websites gingen uit voorzorg tijdelijk offline; NU.nl schreef daar ook over [in een artikel](https://www.nu.nl/binnenland/6413949/websites-komen-langzaamaan-weer-online-na-veiligheidsupdate-van-php.html).

De update is direct doorgevoerd en daarna is het platform weer veilig online gebracht. Er zijn geen gegevens gelekt, aangepast of verloren gegaan. Door snel en zorgvuldig te handelen is alles veilig gebleven.

Veel succes met de komende wedstrijden en vooral veel plezier langs de lijn!

Met sportieve groet,<br>
Antwan van [jeugdvoetbalcoach.nl](https://jeugdvoetbalcoach.nl)

<x-mail::subcopy>
Vragen of ideeën? Laat het weten via het [feedbackformulier](https://jeugdvoetbalcoach.nl#feedback).
Wil je geen updates meer ontvangen? Pas dit dan aan in je [profiel]({{ route('profile.edit') }}).
</x-mail::subcopy>
</x-mail::message>
