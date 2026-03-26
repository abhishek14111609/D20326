<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'competition_id',
        'name',
        'description',
        'logo',
        'leader_id',
        'status',
        'size',
        'score',
        'rank',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'score' => 'integer',
        'rank' => 'integer',
        'size' => 'integer',
    ];

    /**
     * Get the competition that owns the team.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the leader of the team.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * The users that belong to the team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['role', 'status', 'created_at'])
            ->withTimestamps();
    }

    /**
     * Get the submissions for the team.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get the logo URL.
     *
     * @return string
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return asset('images/default-team-logo.png');
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Check if the team is full.
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->members()->count() >= $this->size;
    }

    /**
     * Check if a user is a member of the team.
     *
     * @param  int  $userId
     * @return bool
     */
    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
