<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function show()
    {
        $features = [
            ['title' => 'Automatische Line-ups', 'desc' => 'Genereer 4 kwarten met eerlijke keeper- en bankrotatie op basis van fysiek en positie.'],
            ['title' => 'Multi-Team & Coaches', 'desc' => 'Mogelijk om meerdere teams te coachen en meerdere coaches per team te koppelen.'],
            ['title' => 'Formatiebeheer', 'desc' => 'Gebruik globale presets of gebruik eigen formaties per seizoen.'],
            ['title' => 'Seizoensstructuur', 'desc' => 'Koppel wedstrijden aan seizoensblokken voor een overzichtelijke structuur.'],
            ['title' => 'Deel resultaten', 'desc' => 'Geef ouders gemakkelijk inzicht in opstellingen en resultaten van het team.'],
            ['title' => 'Printklaar', 'desc' => 'De opstelling is gemakkelijk te printen om helemaal voorbereid de wedstrijd in te gaan.'],
        ];

        return view('home', compact('features'));
    }
}
