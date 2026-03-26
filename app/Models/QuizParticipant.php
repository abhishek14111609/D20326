<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizParticipant extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'competition_participant_id',
        'started_at',
        'completed_at',
        'score',
        'total_questions',
        'correct_answers',
        'time_taken',
        'status',
        'metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'time_taken' => 'integer', // in seconds
        'metadata' => 'array',
    ];

    /**
     * The possible status values for a quiz participant.
     */
    public const STATUS_STARTED = 'started';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_DISQUALIFIED = 'disqualified';

    /**
     * Get the quiz that the participant is taking.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the user that is participating in the quiz.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the competition participant record.
     */
    public function competitionParticipant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class);
    }

    /**
     * Get the answers provided by the participant.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Check if the participant has completed the quiz.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->completed_at !== null;
    }

    /**
     * Calculate the score percentage.
     */
    public function getScorePercentage(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }
        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }
}
