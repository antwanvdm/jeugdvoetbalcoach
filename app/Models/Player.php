<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Player extends Model
{
    protected $fillable = [
        'name',
        'position_id',
        'weight',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function footballMatches(): BelongsToMany
    {
        return $this->belongsToMany(FootballMatch::class)
            ->withPivot(['quarter', 'position_id']);
    }

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class);
    }
}
