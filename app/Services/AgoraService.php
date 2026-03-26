<?php

namespace App\Services;

use App\Helpers\AgoraToken;

class AgoraService
{
    public function generateToken($channelName, $uid = "0")
    {
        $appId          = config('services.agora.app_id');
        $appCertificate = config('services.agora.certificate');

        $expireTime = 3600; // 1 hour

        return AgoraToken::generateRtcToken(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $expireTime
        );
    }
}
