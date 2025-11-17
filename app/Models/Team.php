<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'logo',
        'maps_location',
        'invite_code',
    ];

    /**
     * Get the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'is_default', 'joined_at')
            ->orderByPivot('joined_at', 'asc');
    }

    /**
     * Get the hoofdcoach of the team.
     */
    public function hoofdcoach(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 1);
    }

    /**
     * Get the assistenten of the team.
     */
    public function assistenten(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 2);
    }

    /**
     * Get the players for the team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the seasons for the team.
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    /**
     * Get the opponents for the team.
     */
    public function opponents(): HasMany
    {
        return $this->hasMany(Opponent::class);
    }

    /**
     * Get the football matches for the team.
     */
    public function footballMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class);
    }

    /**
     * Get the formations for the team.
     */
    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class);
    }
}
