<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Exception;

class FcmService
{
    protected string $projectId;
    protected string $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = storage_path('app/firebase-service-account.json');

        if (!file_exists($this->serviceAccountPath)) {
            throw new Exception("Firebase service account JSON not found: " . $this->serviceAccountPath);
        }

        $json = json_decode(file_get_contents($this->serviceAccountPath), true);

        if (!isset($json['project_id'])) {
            throw new Exception("Invalid Firebase JSON: project_id missing");
        }

        $this->projectId = $json['project_id'];
    }


    /**
     * Generate Access Token (valid JWT)
     */
    private function getAccessToken()
    {
        $scopes = [
            'https://www.googleapis.com/auth/firebase.messaging'
        ];

        $credentials = new ServiceAccountCredentials(
            $scopes,
            $this->serviceAccountPath
        );
			
        $token = $credentials->fetchAuthToken();

        if (!isset($token['access_token'])) {
            throw new Exception("Failed to generate Firebase access token");
        }
		
        return $token['access_token']; // 🔥 Length will be 100–300+ (correct)
    }


    /**
     * MAIN METHOD CALLED BY YOUR JOB
     */
public function sendToDevice($token, array $payload)
{
    $accessToken = $this->getAccessToken();

    $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

    // FCM v1 requires exact structure
    $finalPayload = [
        "message" => [
            "token" => $token,
            "notification" => $payload['notification'] ?? [],
            "data" => $this->formatData($payload['data'] ?? [])
        ]
    ];
	
    $response = Http::withToken($accessToken)
        ->withHeaders([
            'Content-Type' => 'application/json'
        ])
        ->post($url, $finalPayload);

    \Log::info("FCM Response", [
        "status" => $response->status(),
        "body"   => $response->body(),
        "sent_payload" => $finalPayload
    ]);
	
    return $response->json();
}



    /**
     * OLD METHOD: Send notification with title/body (optional now)
     */
    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        return $this->sendToDevice($fcmToken, [
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
            "data" => $this->formatData($data)
        ]);
    }


    /**
     * Send to multiple devices
     */
    public function sendToDevices(array $tokens, string $title, string $body, array $data = [])
    {
        $responses = [];

        foreach ($tokens as $token) {
            $responses[] = $this->sendNotification($token, $title, $body, $data);
        }
		
        return $responses;
    }


    /**
     * Force all FCM data values to string (required)
     */
    private function formatData(array $data)
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            $formatted[$key] = is_array($value)
                ? json_encode($value)
                : (string) $value;
        }
		
        return $formatted;
    }
}
