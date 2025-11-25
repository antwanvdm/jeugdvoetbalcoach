<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'season_id',
        'user_id',
        'team_id',
        'share_token',
    ];

    protected $casts = [
        'home' => 'boolean',
        'date' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check() && session('current_team_id')) {
                $builder->where('football_matches.team_id', session('current_team_id'));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

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
