<?php

namespace App\Services\Agora;

class AccessToken2
{
    public $appId;
    public $appCertificate;
    public $expire;
    public $services = [];

    public function __construct($appId, $appCertificate, $expire)
    {
        $this->appId = $appId;
        $this->appCertificate = $appCertificate;
        $this->expire = $expire;
    }

    public function addService($service)
    {
        $this->services[] = $service;
    }

    public function build()
    {
        $salt = rand(1, 99999999);
        $ts = time() + $this->expire;

        $payload = pack("N", $ts) . pack("N", $salt);

        foreach ($this->services as $service) {
            $payload .= $service->pack();
        }

        $signature = hash_hmac("sha256", $this->appId . $payload, $this->appCertificate, true);

        return "007" . base64_encode($signature . $payload);
    }
}
