<?php

namespace App\Services;

use App\Models\AudioCall;
use App\Models\User;
use App\Events\CallStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\AgoraService;

class AudioCallService
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * Initiate a new audio call
     */
    public function initiateCall($callerId, $receiverId)
    {
        $receiver = User::findOrFail($receiverId);

        if ($callerId == $receiverId) {
            throw new \Exception('Cannot call yourself');
        }

        // Generate a clean channel name
        $channelName = "duos_" . $callerId . "_" . $receiverId . "_" . Str::random(8);

        // Create call record
        $call = new AudioCall([
            'call_id'       => (string) Str::uuid(),
            'caller_id'     => $callerId,
            'receiver_id'   => $receiverId,
            'status'        => AudioCall::STATUS_INITIATED,
            'agora_channel' => $channelName,
            'started_at'    => now(),
            'is_muted'      => false
        ]);

        // Tokens for caller
        $call->agora_token     = $this->agoraService->generateToken($channelName, $callerId);
       // $call->agora_rtm_token = $this->agoraService->generateRtmToken($callerId);

        $call->save();

        // Notify receiver via event
        event(new CallStatusUpdated($call, 'incoming_call'));

        return $call;
    }

    /**
     * Accept the call
     */
    public function acceptCall($callId, $userId)
    {
        return DB::transaction(function () use ($callId, $userId) {

            $call = AudioCall::where('call_id', $callId)
                ->where('receiver_id', $userId)
                ->where('status', AudioCall::STATUS_INITIATED)
                ->lockForUpdate()
                ->firstOrFail();

            // Token for receiver
            $call->agora_token = $this->agoraService->generateRtcToken(
                $call->agora_channel,
                $userId
            );

            $call->agora_rtm_token = $this->agoraService->generateRtmToken($userId);
            $call->status = AudioCall::STATUS_IN_PROGRESS;
            $call->accepted_at = now();
            $call->save();

            event(new CallStatusUpdated($call, 'call_accepted'));

            return $call;
        });
    }

    /**
     * End a call
     */
    public function endCall($callId, $userId, $status = AudioCall::STATUS_ENDED)
    {
        return DB::transaction(function () use ($callId, $userId, $status) {

            $call = AudioCall::where('call_id', $callId)
                ->where(function ($q) use ($userId) {
                    $q->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->lockForUpdate()
                ->firstOrFail();

            // If call in progress → calculate duration
            if ($call->status === AudioCall::STATUS_IN_PROGRESS) {
                $call->duration = now()->diffInSeconds($call->started_at);
            }

            $call->status = $status;
            $call->ended_at = now();
            $call->save();

            event(new CallStatusUpdated($call, 'call_ended'));

            return $call;
        });
    }

    /**
     * Reject call
     */
    public function rejectCall($callId, $userId)
    {
        return DB::transaction(function () use ($callId, $userId) {

            $call = AudioCall::where('call_id', $callId)
                ->where('receiver_id', $userId)
                ->where('status', AudioCall::STATUS_INITIATED)
                ->lockForUpdate()
                ->firstOrFail();

            $call->status = AudioCall::STATUS_REJECTED;
            $call->ended_at = now();
            $call->save();

            event(new CallStatusUpdated($call, 'call_rejected'));

            return $call;
        });
    }

    /**
     * Mute/unmute feature
     */
    public function muteCall($callId, $userId)
    {
        return $this->updateMute($callId, $userId, true);
    }

    public function unmuteCall($callId, $userId)
    {
        return $this->updateMute($callId, $userId, false);
    }

    protected function updateMute($callId, $userId, $isMuted)
    {
        $call = AudioCall::where('call_id', $callId)
            ->where('status', AudioCall::STATUS_IN_PROGRESS)
            ->where(function ($q) use ($userId) {
                $q->where('caller_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->firstOrFail();

        $call->is_muted = $isMuted;
        $call->save();

        return $call;
    }

    /**
     * Get call stats
     */
    public function getCallStats($callId, $userId)
    {
        $call = $this->getCallDetails($callId, $userId);

        return [
            'call_id'     => $call->call_id,
            'status'      => $call->status,
            'duration'    => $call->duration ?? 0,
            'is_muted'    => $call->is_muted,
            'started_at'  => $call->started_at,
            'ended_at'    => $call->ended_at,
            'caller_id'   => $call->caller_id,
            'receiver_id' => $call->receiver_id,
            'current_duration' =>
                $call->status === AudioCall::STATUS_IN_PROGRESS
                    ? now()->diffInSeconds($call->started_at)
                    : null,
        ];
    }

    /**
     * Get call details
     */
    public function getCallDetails($callId, $userId)
    {
        return AudioCall::where('call_id', $callId)
            ->where(function ($q) use ($userId) {
                $q->where('caller_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->firstOrFail();
    }
}
