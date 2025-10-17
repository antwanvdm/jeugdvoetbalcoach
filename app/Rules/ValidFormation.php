<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidFormation implements Rule
{
    protected int $totalPlayers;

    public function __construct(int $totalPlayers = 0)
    {
        $this->totalPlayers = $totalPlayers;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $lineup = (string) $value;
        $parts = array_filter(explode('-', $lineup), fn($p) => $p !== '');
        if (empty($parts)) {
            return false;
        }

        $sum = 0;
        foreach ($parts as $p) {
            if (!is_numeric($p) || (int)$p < 0) {
                return false;
            }
            $sum += (int)$p;
        }

        if ($this->totalPlayers > 0) {
            return $sum === ($this->totalPlayers - 1);
        }

        // If totalPlayers not provided, just ensure parts are numeric
        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        if ($this->totalPlayers > 0) {
            return 'De optelsom van lineup_formation moet gelijk zijn aan total_players minus 1 (keeper).';
        }
        return 'Ongeldig format. Gebruik formaat zoals 2-1-2.';
    }
}
