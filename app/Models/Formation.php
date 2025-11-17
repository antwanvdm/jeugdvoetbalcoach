<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formation extends Model
{
    protected $fillable = [
        'total_players',
        'lineup_formation',
        'user_id',
        'team_id',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Formations are either global (is_global=true) or belong to the current team
        static::addGlobalScope('available', function (Builder $builder) {
            if (auth()->check()) {
                // Admins can see all formations; skip limiting scope for them
                if (auth()->user()->isAdmin()) {
                    return;
                }
                $builder->where(function ($query) {
                    $query->where('is_global', true)
                          ->orWhere('formations.team_id', session('current_team_id'));
                });
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
}
