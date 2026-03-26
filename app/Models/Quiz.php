<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'competition_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'passing_score',
        'max_attempts',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the competition that owns the quiz.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    /**
     * Get the participants for the quiz.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(QuizParticipant::class);
    }

    /**
     * Check if the quiz is currently active.
     */
public function isActive(): bool
{
    return (int) $this->is_active === 1;
}

}
