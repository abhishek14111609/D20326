<?php

namespace App\Services;

use App\Services\Agora\RtcTokenBuilder2;

class AgoraTokenService
{
    public function generate($channelName, $uid)
    {
        $appId = config('agora.app_id');
        $appCertificate = config('agora.certificate');
        $expire = 3600;

        return RtcTokenBuilder2::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            1,
            time() + $expire
        );
    }
}
