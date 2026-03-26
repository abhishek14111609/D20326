<?php

namespace App\Services\Agora;

class AgoraService
{
    public function generateRtcToken($channelName, $uid, $expire = 3600)
    {
        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        $token = new AccessToken2($appId, $appCertificate, $channelName, $uid, $expire);
        return $token->build();
    }
}
