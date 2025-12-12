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
        'real_name',
        'location',
        'address',
        'website',
        'logo',
        'latitude',
        'longitude',
        'kit_reference'
    ];

    public function footballMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => $attributes['real_name'] ?? $value
        );
    }

    protected function systemName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => $attributes['name']
        );
    }

    protected function kitUrl(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => $attributes['kit_reference']
                ? asset('storage/kits/t_' . $attributes['kit_reference'] . '.png')
                : null
        );
    }

    protected function locationMapsLink(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => 'https://www.google.com/maps?q=' . urlencode($attributes['address'] ?? ($attributes['latitude'] . ',' . $attributes['longitude']))
        );
    }
}
