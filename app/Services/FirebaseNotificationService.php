<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    private $tokenService;

    public function __construct(FirebaseTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        $accessToken = $this->tokenService->getAccessToken();
        $projectId = config('services.firebase.project_id');
		
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $title,
                    "body"  => $body,
                ],
                "data" => $data
            ]
        ];
		
        $response = Http::withToken($accessToken)
            ->post($url, $payload);
		
        if ($response->failed()) {
            return [
                "success" => false,
                "error"   => $response->body(),
            ];
        }

        return [
            "success" => true,
            "response" => $response->json(),
        ];
    }
}
