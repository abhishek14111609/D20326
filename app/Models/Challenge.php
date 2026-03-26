<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use DB;

class Challenge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'start_date',
        'end_date',
        'status',
        'type',
        'target_count',
        'reward_points',
        'rules',
        'is_featured',
        'sort_order',
        'metadata'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'metadata' => 'array',
        'target_count' => 'integer',
        'reward_points' => 'integer',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
	public function getActiveParticipantsCountAttribute()
    {
        return DB::table('challenge_participants')
            ->join('users', 'users.id', '=', 'challenge_participants.user_id')
            ->where('challenge_participants.challenge_id', $this->id)
            ->where('challenge_participants.status', 'active')
            ->whereNull('users.deleted_at')
            ->count();
    }

	public function participants()
	{
		return $this->hasMany(ChallengeParticipant::class);
	}


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
            ->where('status', '!=', 'cancelled');
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function isUpcoming(): bool
    {
        return $this->start_date > now() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->end_date < now();
    }

    public function daysRemaining(): ?int
    {
        if ($this->end_date < now()) {
            return null;
        }
        
        return now()->diffInDays($this->end_date);
    }
}
