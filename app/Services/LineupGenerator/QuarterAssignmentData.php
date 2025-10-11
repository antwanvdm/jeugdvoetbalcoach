<?php

namespace App\Services\LineupGenerator;

use Illuminate\Support\Collection;

/**
 * Data class to hold quarter assignment state
 */
class QuarterAssignmentData
{
    private array $assignments = [];
    private Collection $selectedPlayers;
    private int $quarter;

    public function __construct(int $quarter)
    {
        $this->quarter = $quarter;
        $this->selectedPlayers = collect();
    }

    public function addAssignment(int $playerId, ?int $positionId): self
    {
        $this->assignments[$playerId] = ['quarter' => $this->quarter, 'position_id' => $positionId];
        return $this;
    }

    public function addSelectedPlayer(int $playerId, string $role): self
    {
        $this->selectedPlayers[$playerId] = $role;
        return $this;
    }

    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function getSelectedPlayers(): Collection
    {
        return $this->selectedPlayers;
    }

    public function getSelectedPlayerIds(): array
    {
        return $this->selectedPlayers->keys()->all();
    }

    public function isPlayerSelected(int $playerId): bool
    {
        return isset($this->selectedPlayers[$playerId]);
    }

    public function isPlayerAssigned(int $playerId): bool
    {
        return isset($this->assignments[$playerId]);
    }

    public function getQuarter(): int
    {
        return $this->quarter;
    }
}
