<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCall extends Model
{
    const STATUS_INITIATED = 'initiated';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ENDED = 'ended';
    const STATUS_MISSED = 'missed';
    const STATUS_BUSY = 'busy';
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'call_id',
        'caller_id',
        'receiver_id',
        'agora_channel',
        'agora_token',
        'agora_rtm_token',
        'status',
        'is_muted',
        'is_video_enabled',
        'started_at',
        'ended_at',
        'duration',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_muted' => 'boolean',
        'is_video_enabled' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the caller that owns the video call.
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the receiver that owns the video call.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Check if the call has ended.
     */
    public function hasEnded(): bool
    {
        return in_array($this->status, [self::STATUS_ENDED, self::STATUS_REJECTED, self::STATUS_MISSED]);
    }

    /**
     * Check if the call is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACCEPTED && !$this->hasEnded();
    }

    /**
     * Check if the call is ready for connection.
     */
    public function isReadyForConnection(): bool
    {
        return $this->agora_channel && 
               $this->agora_token && 
               $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * End the call and calculate duration.
     */
    public function endCall(string $status = self::STATUS_ENDED): void
    {
        $this->status = $status;
        $this->ended_at = now();
        
        if ($this->started_at) {
            $this->duration = $this->ended_at->diffInSeconds($this->started_at);
        }
        
        $this->save();
    }
}
