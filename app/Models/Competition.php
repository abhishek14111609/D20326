<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Competition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'banner_image',
        'registration_start',
        'registration_end',
        'start_date',
        'end_date',
        'status',
        'competition_type',
        'tags',
        'timezone',
        'max_participants',
        'min_participants',
        'entry_fee',
        'prizes',
        'rules',
        'judging_criteria',
        'is_featured',
        'metadata'
    ];

    protected $casts = [
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'metadata' => 'array',
        'description' => 'string',
        'prizes' => 'array',
        'tags' => 'array',
        'max_participants' => 'integer',
        'min_participants' => 'integer',
        'entry_fee' => 'integer',
        'competition_type' => 'string'
    ];

    protected $dates = [
        'registration_start',
        'registration_end',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
	
    // Relationships
   public function getActiveParticipantsCountAttribute()
    {
        return DB::table('competition_participants')
            ->join('users', 'users.id', '=', 'competition_participants.user_id')
            ->where('competition_participants.competition_id', $this->id)
            ->where('competition_participants.status', 'active')
            ->whereNull('users.deleted_at')
            ->count();
    }

	public function participants()
	{
		return $this->hasMany(CompetitionParticipant::class);
	}
	
	public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }



    // Scopes
    public function scopeRegistrationOpen($query)
    {
        return $query->where('status', 'registration_open')
            ->where('registration_start', '<=', now())
            ->where('registration_end', '>=', now());
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helper Methods
    public function isRegistrationOpen(): bool
    {
        return $this->status === 'registration_open' && 
               $this->registration_start <= now() && 
               $this->registration_end >= now();
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function isUpcoming(): bool
    {
        return in_array($this->status, ['upcoming', 'registration_open']) && 
               $this->start_date > now();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->end_date < now();
    }

    public function registrationStatus(): string
    {
        if ($this->status === 'completed' || $this->end_date < now()) {
            return 'ended';
        }
        
        if ($this->isRegistrationOpen()) {
            return 'open';
        }
        
        if ($this->registration_start > now()) {
            return 'upcoming';
        }
        
        return 'closed';
    }
}
