<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Player;

class LineupGenerator
{
    private array $players = [];

    public function generate(FootballMatch $match): void
    {
        $this->players = Player::inRandomOrder()->get();

        $keepers = $this->pickKeepers();
        $benchPlan = $this->makeBenchPlan($keepers);
        $lineups = $this->assignQuarters($keepers, $benchPlan);

        foreach ($lineups as $quarter => $attach) {
            $match->players()->attach($attach);
        }
    }

    protected function pickKeepers()
    {
        // Extracteer keeperselectie in een apart algoritme
    }

    protected function makeBenchPlan($keepers)
    {
        // Maak schema voor wie wanneer op de bank zit
    }

    protected function assignQuarters($keepers, $benchPlan)
    {
        // Koppel posities per kwart
    }

    private function calculateWeightBalance($playerIds)
    {
        if (empty($playerIds)) {
            return 0;
        }

        $selectedPlayers = $this->players->whereIn('id', $playerIds);
        $weights = $selectedPlayers->pluck('weight')->countBy();
        $totalPlayers = count($playerIds);

        if ($totalPlayers <= 1) {
            return 0;
        }

        // Calculate penalty for having too many players with same weight
        $penalty = 0;
        foreach ($weights as $weight => $count) {
            if ($count > 2) { // More than 2 players with same weight is less desirable
                $penalty += ($count - 2) * 10; // Heavy penalty for clustering
            } elseif ($count > 1) {
                $penalty += ($count - 1) * 2; // Light penalty for pairs
            }
        }

        // Add variance component for overall distribution
        $idealCountPerWeight = $totalPlayers / max(1, $weights->count());
        $variance = 0;
        foreach ($weights as $count) {
            $variance += pow($count - $idealCountPerWeight, 2);
        }

        return $penalty + ($variance * 0.5);
    }
}
