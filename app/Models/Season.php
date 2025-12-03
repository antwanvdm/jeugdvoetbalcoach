<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Season extends Model
{
    protected $fillable = ['year', 'part', 'start', 'end', 'formation_id', 'user_id', 'team_id', 'track_goals', 'share_token'];
    protected $casts = [
        'start' => 'date',
        'end' => 'date',
        'track_goals' => 'boolean',
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

    public static function getCurrent(?Collection $seasons = null): ?Season
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

    /**
     * Get top scorers for this season (players with most goals).
     */
    public function topScorers(int $limit = 10)
    {
        return Player::query()
            ->select('players.*')
            ->selectRaw('COUNT(match_goals.id) as goals_count')
            ->join('match_goals', 'players.id', '=', 'match_goals.player_id')
            ->join('football_matches', 'match_goals.football_match_id', '=', 'football_matches.id')
            ->where('football_matches.season_id', $this->id)
            ->where('football_matches.team_id', $this->team_id)
            ->groupBy('players.id')
            ->orderByDesc('goals_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top assist providers for this season.
     */
    public function topAssisters(int $limit = 10)
    {
        return Player::query()
            ->select('players.*')
            ->selectRaw('COUNT(match_goals.id) as assists_count')
            ->join('match_goals', 'players.id', '=', 'match_goals.assist_player_id')
            ->join('football_matches', 'match_goals.football_match_id', '=', 'football_matches.id')
            ->where('football_matches.season_id', $this->id)
            ->where('football_matches.team_id', $this->team_id)
            ->groupBy('players.id')
            ->orderByDesc('assists_count')
            ->limit($limit)
            ->get();
    }
}
