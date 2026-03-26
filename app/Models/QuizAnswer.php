<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'quiz_participant_id',
        'question_id',
        'answer',
        'is_correct',
        'time_taken',
        'points_earned',
    ];

    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
        'time_taken' => 'integer', // in seconds
        'points_earned' => 'integer',
    ];

    /**
     * Get the quiz participant that owns the answer.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(QuizParticipant::class, 'quiz_participant_id');
    }

    /**
     * Get the question that this answer is for.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Get the quiz that this answer belongs to.
     */
    public function quiz(): BelongsTo
    {
        return $this->participant->quiz();
    }

    /**
     * Get the user who provided this answer.
     */
    public function user(): BelongsTo
    {
        return $this->participant->user();
    }

    /**
     * Check if the answer was submitted after the time limit.
     */
    public function isTimedOut(): bool
    {
        if (!$this->question || !$this->question->time_limit) {
            return false;
        }
        
        return $this->time_taken > $this->question->time_limit;
    }

    /**
     * Get the formatted answer text.
     */
    public function getFormattedAnswer(): string
    {
        if (is_array($this->answer)) {
            return implode(', ', $this->answer);
        }
        return (string) $this->answer;
    }
}
