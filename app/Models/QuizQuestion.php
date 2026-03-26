<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'question_type',
        'options',
        'correct_answer',
        'points',
        'time_limit',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'integer',
        'time_limit' => 'integer',
        'order' => 'integer',
    ];

    // =========================
    // RELATIONS
    // =========================
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    // =========================
    // HELPERS
    // =========================

    /**
     * Return bootstrap badge color based on question type
     */
    public function getTypeColor(): string
    {
        return match ($this->question_type) {
            'multiple_choice' => 'primary',
            'true_false'      => 'success',
            'short_answer'    => 'warning',
            'essay'           => 'dark',
            default           => 'secondary',
        };
    }

    /**
     * Get correct option indexes
     */
    public function correctOptions(): array
    {
        if (!in_array($this->question_type, ['multiple_choice', 'true_false'])) {
            return [];
        }

        return $this->correct_answer ?? [];
    }

    /**
     * Check answer correctness
     */
    public function isAnswerCorrect($answer): bool
    {
        if ($this->question_type === 'multiple_choice') {
            sort($answer);
            $correct = $this->correctOptions();
            sort($correct);
            return $answer === $correct;
        }

        if ($this->question_type === 'true_false') {
            return ($this->correctOptions()[0] ?? null) == $answer;
        }

        return false; // short_answer / essay manual
    }
	
	/**
 * Human readable question type name
 */
public function getTypeName(): string
{
    return match ($this->question_type) {
        'multiple_choice' => 'Multiple Choice',
        'true_false'      => 'True / False',
        'short_answer'    => 'Short Answer',
        'essay'           => 'Essay',
        default           => 'Unknown',
    };
}

}
