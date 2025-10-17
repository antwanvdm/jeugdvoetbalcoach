<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Season extends Model
{
    protected $fillable = ['year', 'part', 'start', 'end', 'formation_id'];
    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

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
