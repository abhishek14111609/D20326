<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Exception;

class FirebaseTokenService
{
    private $credentials;
    private $token;
    private $expiryTime;

    public function __construct()
    {
        $path = storage_path('app/firebase-service-account.json');
        
        if (!file_exists($path)) {
            throw new Exception("Firebase service account file missing: $path");
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $this->credentials = new ServiceAccountCredentials($scopes, $path);
    }

    public function getAccessToken()
    {
        if ($this->token && $this->expiryTime > time()) {
            return $this->token;
        }

        $result = $this->credentials->fetchAuthToken();
		
        if (!isset($result['access_token'])) {
            throw new Exception("Unable to generate Firebase access token.");
        }

        $this->token = $result['access_token'];
		
        $this->expiryTime = time() + $result['expires_in'] - 60;

        return $this->token;
    }
}
