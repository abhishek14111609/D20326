<?php

namespace App\Helpers;

class AgoraToken
{
    public static function generateRtcToken($appId, $appCertificate, $channelName, $uid, $expireTimestamp)
    {
        $current = time();
        $expire = $current + $expireTimestamp;

        $content = pack("V", $expire) . pack("V", $current) . $channelName . $uid;
        $signature = hash_hmac('sha1', $content, $appCertificate, true);

        $version = "007";
        $token = $version . base64_encode($signature . $content);

        return $token;
    }
}
