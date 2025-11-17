<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opponent extends Model
{
    protected $fillable = [
        'name',
        'location',
        'logo',
        'latitude',
        'longitude',
        'user_id',
        'team_id',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check() && session('current_team_id')) {
                $builder->where('opponents.team_id', session('current_team_id'));
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

    public function footballMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class);
    }

    protected function locationMapsLink(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => 'https://www.google.com/maps?q=' . urlencode($attributes['latitude'] . ',' . $attributes['longitude'])
        );
    }
}
