<?php

namespace App\Services\AgoraToken;

class AgoraService
{
    public function generateRtcToken($channelName, $uid)
    {
        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        return RtcTokenBuilder::buildToken(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            3600
        );
    }
}
