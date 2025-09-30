<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opponent extends Model
{
    protected $fillable = [
        'name',
        'location',
        'logo',
        'latitude',
        'longitude',
    ];

    public function footballMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class);
    }

    protected function locationMapsLink(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, $attributes) => 'https://www.google.com/maps?q=' . urlencode($attributes['latitude'] . ',' . $attributes['longitude'])
        );
    }
}
