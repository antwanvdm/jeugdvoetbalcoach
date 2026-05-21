<x-mail::message>
# Beste {{ $userName }},

Er is een nieuwe update toegevoegd die alle gespeelde seizoensfases samenvoegd in één mooi overzicht. Maar eerst even een korte uitleg over iets waar soms verwarring over ontstaat: het **startjaar** van een seizoen en de betekenis van **fases**.

### Het startjaar: altijd vanuit het voetbalseizoen

Wanneer je een nieuw seizoen aanmaakt, vul je een **startjaar** in, bijvoorbeeld **2025**. Dat staat dan voor het voetbalseizoen **2025/2026**, dus ruwweg van augustus/september 2025 tot en met mei/juni 2026.

Dit is hoe voetbalclubs en competities in Nederland over seizoenen redeneren, en zo werkt het ook in de app. Het startjaar is altijd het jaar waarin het seizoen begint.

### Fases: het seizoen in stukjes

De KNVB verdeelt het seizoen voor O7 t/m O12 in vier kortere fases. Na elke fase volgt een nieuwe indeling en kunnen ook nieuwe teams instappen. Meer over de planning per district vind je op de
[KNVB-website](https://www.knvb.nl/assist-wedstrijdsecretarissen/veldvoetbal/seizoensplanning/fasenvoetbal-jeugd). Binnen de seizoenen op Jeugdvoetbalcoach.nl noemen we elke KNVB-fase gewoon een **fase**.

Elke fase heeft zijn eigen speelschema, opstelling en statistieken. Zo houd je alles netjes gescheiden, maar zie je toch het grote plaatje.
Heb je de fases of startdatum niet goed genoteerd? Geen probleem, dit kun je gewoon in je seizoensoverzicht bewerken zodat je data weer goed staat.

### Nieuw: automatisch jaaroverzicht

Heb je **twee of meer fases** aangemaakt met hetzelfde startjaar? Dan verschijnt er automatisch een **Jaaroverzicht** bovenaan je seizoenenlijst.

Met één klik zie je alle fases samengevoegd:

- Totaal aantal wedstrijden over het hele seizoen
- Gecombineerde winst/gelijk/verlies statistieken
- Totaal doelsaldo over alle fases
- Alle wedstrijden op chronologische volgorde
- Gecombineerde topscorers en assists (als doelpunten bijgehouden worden)

Je kunt dit overzicht ook gewoon **delen met ouders** via een eigen deellink, net zoals je dat al gewend bent bij losse seizoensfases en wedstrijden.

<x-mail::button :url="'https://jeugdvoetbalcoach.nl/seasons'">
    Bekijk mijn seizoenen
</x-mail::button>

Succes met de afronding van dit voetbalseizoen en een hele fijne zomer toegewenst!

Met sportieve groet,<br>
Antwan van [jeugdvoetbalcoach.nl](https://jeugdvoetbalcoach.nl)

<x-mail::subcopy>
Vragen of ideeën? Laat het weten via het [feedbackformulier](https://jeugdvoetbalcoach.nl#feedback).
Wil je geen updates meer ontvangen? Pas dit dan aan in je [profiel]({{ route('profile.edit') }}).
</x-mail::subcopy>
</x-mail::message>
