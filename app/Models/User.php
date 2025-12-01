<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 1;
    }

    /**
     * Get the teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role', 'is_default', 'joined_at', 'label')
            ->orderByPivot('joined_at', 'asc');
    }

    /**
     * Get the default team for the user.
     */
    public function defaultTeam(): ?Team
    {
        return $this->teams()->wherePivot('is_default', true)->first();
    }

    /**
     * Check if the user is a member of the given team.
     */
    public function isMemberOf(Team $team): bool
    {
        return $this->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Check if the user is the hoofdcoach of the given team.
     */
    public function isHeadCoach(Team $team): bool
    {
        return $this->teams()
            ->where('teams.id', $team->id)
            ->wherePivot('role', 1)
            ->exists();
    }

    /**
     * Get the players for the user.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the seasons for the user.
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    /**
     * Get the opponents for the user.
     */
    public function opponents(): HasMany
    {
        return $this->hasMany(Opponent::class);
    }

    /**
     * Get the football matches for the user.
     */
    public function footballMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class);
    }

    /**
     * Get the formations for the user.
     */
    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class);
    }
}
