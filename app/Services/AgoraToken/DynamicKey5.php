<?php

namespace App\Services\AgoraToken;

class DynamicKey5
{
    public static function generate($appID, $appCertificate, $channelName, $uid, $expireTimestamp)
    {
        $version = "005";

        $unixTs = time();
        $salt = rand(1, 99999999);

        $signature = hash("sha256", $appID . $appCertificate . $channelName . $unixTs . $salt . $uid);

        $content = pack("N", $unixTs)
            . pack("N", $salt)
            . pack("N", $uid)
            . pack("N", $expireTimestamp)
            . $signature;

        return $version . base64_encode($content);
    }
}
