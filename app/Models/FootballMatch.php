<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FootballMatch extends Model
{
    protected $fillable = [
        'opponent_id',
        'home',
        'goals_scored',
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
            ->withPivot(['quarter', 'position_id']);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    protected function result(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, $attributes) {
                if (is_null($attributes['goals_scored']) || is_null($attributes['goals_conceded'])) {
                    return 'O';
                } else {
                    return $attributes['goals_scored'] > $attributes['goals_conceded'] ? 'W' : ($attributes['goals_conceded'] > $attributes['goals_scored'] ? 'L' : 'D');
                }
            }
        );
    }
}
