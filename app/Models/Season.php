<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Season extends Model
{
    protected $fillable = ['year', 'part', 'start', 'end', 'formation_id', 'user_id', 'team_id'];
    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check() && session('current_team_id')) {
                $builder->where('seasons.team_id', session('current_team_id'));
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

    public function players()
    {
        return $this->belongsToMany(Player::class);
    }

    public function footballMatches()
    {
        return $this->hasMany(FootballMatch::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public static function getCurrent(?Collection $seasons = null): Season
    {
        if (!$seasons) {
            $seasons = self::all();
        }

        $today = Carbon::today();
        $activeSeason = $seasons->first(fn(Season $s) => $s->start->lte($today) && $s->end->gte($today));
        if (!$activeSeason) {
            $activeSeason = $seasons
                ->filter(fn(Season $s) => $s->end->lt($today))
                ->sortByDesc(fn(Season $s) => $s->end)
                ->first();
        }
        return $activeSeason;
    }
}
