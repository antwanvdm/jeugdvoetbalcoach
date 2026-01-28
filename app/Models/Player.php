<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Player extends Model
{
    protected $fillable = [
        'name',
        'position_id',
        'weight',
        'wants_to_keep',
        'user_id',
        'team_id',
    ];

    protected function casts(): array
    {
        return [
            'wants_to_keep' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check() && session('current_team_id')) {
                $builder->where('players.team_id', session('current_team_id'));
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
