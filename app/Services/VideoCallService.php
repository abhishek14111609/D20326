<?php

namespace App\Services;

use App\Models\VideoCall;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoCallService
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * Initiate a new video call
     */
    public function initiateCall(int $callerId, int $receiverId): VideoCall
    {
        return DB::transaction(function () use ($callerId, $receiverId) {
            $call = new VideoCall([
                'call_id' => 'vc_' . Str::uuid()->toString(),
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'agora_channel' => 'video_' . Str::random(16),
                'status' => VideoCall::STATUS_INITIATED,
                'is_video_enabled' => true,
            ]);

            // Generate Agora tokens
            $this->generateAgoraTokens($call);
            
            $call->save();
            
            return $call;
        });
    }

    /**
     * Accept an incoming video call
     */
    public function acceptCall(string $callId, int $userId): VideoCall
    {
        return DB::transaction(function () use ($callId, $userId) {
            $call = $this->getCallDetails($callId, $userId);
            
            if ($call->receiver_id !== $userId) {
                throw new \Exception('Only the receiver can accept the call');
            }

            if ($call->status !== VideoCall::STATUS_INITIATED) {
                throw new \Exception('Call is not in a state that can be accepted');
            }

            $call->status = VideoCall::STATUS_ACCEPTED;
            $call->started_at = now();
            
            // Regenerate tokens with updated privileges
            $this->generateAgoraTokens($call);
            
            $call->save();
            
            return $call;
        });
    }

    /**
     * End a video call
     */
    public function endCall(string $callId, int $userId, string $status = VideoCall::STATUS_ENDED): VideoCall
    {
        return DB::transaction(function () use ($callId, $userId, $status) {
            $call = $this->getCallDetails($callId, $userId);
            
            if (!in_array($call->status, [VideoCall::STATUS_ACCEPTED, VideoCall::STATUS_INITIATED])) {
                throw new \Exception('Call is not in a state that can be ended');
            }

            $call->endCall($status);
            
            return $call;
        });
    }

    /**
     * Toggle video on/off during a call
     */
    public function toggleVideo(string $callId, int $userId, bool $enable): VideoCall
    {
        return DB::transaction(function () use ($callId, $userId, $enable) {
            $call = $this->getCallDetails($callId, $userId);
            
            if (!$call->isActive()) {
                throw new \Exception('Cannot toggle video on an inactive call');
            }

            $call->is_video_enabled = $enable;
            $call->save();
            
            return $call;
        });
    }

    /**
     * Mute a video call
     *
     * @param string $callId
     * @param int $userId
     * @return VideoCall
     * @throws \Exception
     */
    public function muteCall(string $callId, int $userId): VideoCall
    {
        return DB::transaction(function () use ($callId, $userId) {
            $call = $this->getCallDetails($callId, $userId);
            
            if (!$call->isActive()) {
                throw new \Exception('Cannot mute an inactive call');
            }

            if ($call->is_muted) {
                throw new \Exception('Call is already muted');
            }

            $call->is_muted = true;
            $call->save();
            
            return $call;
        });
    }

    /**
     * Unmute a video call
     *
     * @param string $callId
     * @param int $userId
     * @return VideoCall
     * @throws \Exception
     */
    public function unmuteCall(string $callId, int $userId): VideoCall
    {
        return DB::transaction(function () use ($callId, $userId) {
            $call = $this->getCallDetails($callId, $userId);
            
            if (!$call->isActive()) {
                throw new \Exception('Cannot unmute an inactive call');
            }

            if (!$call->is_muted) {
                throw new \Exception('Call is not muted');
            }

            $call->is_muted = false;
            $call->save();
            
            return $call;
        });
    }

    /**
     * Get Agora configuration for a video call
     */
    public function getAgoraConfig(string $callId, int $userId): array
    {
        $call = $this->getCallDetails($callId, $userId);
        
        $isCaller = ($userId === $call->caller_id);
        $token = $isCaller ? $call->agora_token : $call->agora_rtm_token;
        $uid = $isCaller ? $call->caller_id : $call->receiver_id;

        return [
            'app_id' => config('services.agora.app_id'),
            'channel' => $call->agora_channel,
            'token' => $token,
            'uid' => $uid,
            'is_caller' => $isCaller,
            'caller_id' => $call->caller_id,
            'receiver_id' => $call->receiver_id,
            'is_video_enabled' => $call->is_video_enabled,
        ];
    }

    /**
     * Generate Agora tokens for a call
     */
    protected function generateAgoraTokens(VideoCall $call): void
    {
        // Generate RTC token for voice/video
        $call->agora_token = $this->agoraService->generateToken(
            $call->agora_channel,
            $call->caller_id,
            'publisher' // Using publisher role for both audio and video
        );

        // Generate RTM token for signaling
        if (method_exists($this->agoraService, 'generateRtmToken')) {
            $call->agora_rtm_token = $this->agoraService->generateRtmToken(
                (string) $call->caller_id
            );
        } else {
            // Fallback to RTC token if RTM is not available
            $call->agora_rtm_token = $call->agora_token;
        }
    }

    /**
     * Get call details with authorization
     */
    public function getCallDetails(string $callId, int $userId): VideoCall
    {
        $call = VideoCall::where('call_id', $callId)
            ->where(function($query) use ($userId) {
                $query->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->firstOrFail();

        return $call;
    }
}
