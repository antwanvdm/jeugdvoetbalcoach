@props(['match', 'showDash' => true])

@php
    // Normaliseer result code zodat ook lowercase/"L" worden meegenomen
    $resultCode = strtoupper($match->result ?? '');

    // Bepaal de kleur op basis van resultaat
    $resultClass = match($resultCode) {
        'W' => 'text-green-600 dark:text-green-400', // Winst
        'V', 'L' => 'text-red-600 dark:text-red-400', // Verlies (V/L)
        'G', 'D' => 'text-blue-600 dark:text-blue-300',   // Gelijkspel (G/D)
        default => 'text-gray-500 dark:text-gray-400' // Geen resultaat
    };

    // Bepaal de score
    $score = null;
    if ($resultCode !== 'O') {
        if ($match->home) {
            $score = $match->goals_scored . ' - ' . $match->goals_conceded;
        } else {
            $score = $match->goals_conceded . ' - ' . $match->goals_scored;
        }
    } else {
        $score = $showDash ? '-' : null;
    }
@endphp

@if($score)
    <span class="font-bold {{ $resultClass }}" {{ $attributes }}>{{ $score }}</span>
@endif
