<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchGoal extends Model
{
    protected $fillable = [
        'football_match_id',
        'player_id',
        'assist_player_id',
        'minute',
        'subtype',
        'notes',
    ];

    protected $casts = [
        'minute' => 'integer',
    ];

    public function footballMatch(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function assistPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'assist_player_id');
    }
}
