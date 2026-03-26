<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChallengeParticipant extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'challenge_participants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challenge_id',
        'user_id',
        'status',
        'completed_at',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the challenge that owns the participant.
     */

	public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Get the user that owns the participant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
