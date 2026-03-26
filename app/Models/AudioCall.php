<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioCall extends Model
{
    use HasFactory, SoftDeletes;

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
     * @var array
     */
    protected $fillable = [
        'call_id',
        'caller_id',
        'receiver_id',
        'status',
        'agora_channel',
        'agora_token',
        'agora_rtm_token',
        'started_at',
        'accepted_at',
        'ended_at',
        'duration',
        'is_muted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'accepted_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_muted' => 'boolean',
        'duration' => 'integer',
    ];

    // Status constants
    public const STATUS_IN_PROGRESS = 'in_progress';

    /**
     * Get the caller of the call.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the receiver of the call.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Check if the call is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'in_progress' && $this->accepted_at && !$this->ended_at;
    }

    /**
     * Check if the call is in progress.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if the call has ended.
     *
     * @return bool
     */
    public function hasEnded(): bool
    {
        return (bool) $this->ended_at;
    }

    /**
     * Check if the call is ready for connection.
     *
     * @return bool
     */
    public function isReadyForConnection(): bool
    {
        return !empty($this->agora_channel) && !empty($this->agora_token);
    }

    /**
     * Get the other participant in the call.
     */
    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->caller_id === $userId) {
            return $this->receiver;
        }
        
        if ($this->receiver_id === $userId) {
            return $this->caller;
        }
        
        return null;
    }

    /**
     * Get the role of a user in the call.
     */
    public function getUserRole(int $userId): ?string
    {
        if ($this->caller_id === $userId) {
            return 'caller';
        }
        
        if ($this->receiver_id === $userId) {
            return 'receiver';
        }
        
        return null;
    }

    /**
     * Get the Agora configuration for this call.
     */
    public function getAgoraConfig(): array
    {
        return [
            'channel' => $this->agora_channel,
            'token' => $this->agora_token,
            'rtm_token' => $this->agora_rtm_token,
            'call_id' => $this->call_id,
        ];
    }
}
