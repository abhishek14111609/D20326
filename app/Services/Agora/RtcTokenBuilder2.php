<?php

namespace App\Services\Agora;

class RtcTokenBuilder2
{
    const ROLE_PUBLISHER = 1;

    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $expire)
    {
        $token = new AccessToken2($appId, $appCertificate, $expire);

        $service = new ServiceRtc($channelName, $uid);
        $service->addPrivilege(ServiceRtc::PRIVILEGE_JOIN_CHANNEL, $expire);

        if ($role == self::ROLE_PUBLISHER) {
            $service->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM, $expire);
            $service->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_VIDEO_STREAM, $expire);
            $service->addPrivilege(ServiceRtc::PRIVILEGE_PUBLISH_DATA_STREAM, $expire);
        }

        $token->addService($service);
        return $token->build();
    }
}

class ServiceRtc
{
    const PRIVILEGE_JOIN_CHANNEL = 1;
    const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    const PRIVILEGE_PUBLISH_DATA_STREAM = 4;

    public $channelName;
    public $uid;
    public $privileges = [];

    public function __construct($channelName, $uid)
    {
        $this->channelName = $channelName;
        $this->uid = $uid;
    }

    public function addPrivilege($privilege, $expire)
    {
        $this->privileges[$privilege] = $expire;
    }

    public function pack()
    {
        $data = pack("n", strlen($this->channelName)) . $this->channelName;
        $data .= pack("N", $this->uid);
        $data .= pack("n", count($this->privileges));

        foreach ($this->privileges as $key => $exp) {
            $data .= pack("n", $key) . pack("N", $exp);
        }

        return $data;
    }
}
