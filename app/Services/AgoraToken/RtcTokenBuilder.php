<?php

namespace App\Services\AgoraToken;

class RtcTokenBuilder
{
    public static function buildToken($appId, $appCertificate, $channelName, $uid, $expireInSeconds = 3600)
    {
        $expireTimestamp = time() + $expireInSeconds;

        $token = DynamicKey5::generate(
            $appId,
            $appCertificate,
            $channelName,
            intval($uid),
            $expireTimestamp
        );

        return "007" . $token;
    }
}
