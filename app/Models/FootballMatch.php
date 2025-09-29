<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FootballMatch extends Model
{
    protected $fillable = [
        'opponent_id',
        'home',
        'goals_scores',
        'goals_conceded',
        'date',
    ];

    protected $casts = [
        'home' => 'boolean',
        'date' => 'datetime',
    ];

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)
            ->withPivot(['quarter', 'position_id'])
            ->withTimestamps();
    }
}
