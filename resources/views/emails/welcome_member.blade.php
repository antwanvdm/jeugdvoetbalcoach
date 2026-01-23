<x-mail::message>
# Beste {{ $userName }},

Welkom bij jeugdvoetbalcoach.nl! Tof dat je gebruik maakt van ons platform om het coachen van je team makkelijker te maken.

### Belangrijke update: Probleem met spelers opgelost
Er zat een kleine fout in het systeem (dank voor het melden!) waardoor je bij het aanmaken van een seizoen met een startdatum in de toekomst de spelers niet meer kon zien en geen wedstrijden kon aanmaken.

Dit is inmiddels volledig opgelost. Mocht je hier tegenaan zijn gelopen, dan kun je nu weer zorgeloos verder met het plannen van je volgende fase!

### Nieuw: Dark Mode ondersteuning
Voor de coaches die 's avonds laat nog even de opstelling willen maken: er is nu volledige ondersteuning voor **dark mode**. Het systeem volgt automatisch de instellingen van je apparaat, zodat het ook in de late uurtjes prettig werkt.
(Handmatig aan te passen via het zonnetje/maantje-icoon in het menu.)

### Jouw feedback is goud waard
Hoewel het systeem flink is doorgetest, kunnen er altijd kleine dingetjes boven water komen zodra jullie ermee aan de slag gaan. Heb je vragen, suggesties of kom je een foutje tegen? Meld dit dan vooral via het feedbackformulier op de website.

<x-mail::button :url="'https://jeugdvoetbalcoach.nl'">
Ga naar jeugdvoetbalcoach.nl
</x-mail::button>

Veel succes met je team!

Met sportieve groet,<br>
Antwan van [jeugdvoetbalcoach.nl](https://jeugdvoetbalcoach.nl)

<x-mail::subcopy>
Vragen of ideeën? Laat het weten via het [contactformulier](https://jeugdvoetbalcoach.nl#feedback).
Wil je geen updates meer ontvangen? Pas dit dan aan in je [profiel]({{ route('profile.edit') }}).
</x-mail::subcopy>
</x-mail::message>
