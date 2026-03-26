<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * Authenticate with Google
     */
    public function authenticateWithGoogle(array $data): array
    {
        $accessToken = $data['access_token'];
        
        // Verify Google token and get user info
        $response = Http::get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'access_token' => $accessToken
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Invalid Google access token');
        }
        
        $googleUser = $response->json();
        
        return $this->handleSocialUser([
            'provider' => 'google',
            'provider_id' => $googleUser['id'],
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'avatar' => $googleUser['picture'] ?? null,
        ], $data);
    }

    /**
     * Authenticate with Facebook
     */
    public function authenticateWithFacebook(array $data): array
    {
        $accessToken = $data['access_token'];
        
        // Verify Facebook token and get user info
        $response = Http::get('https://graph.facebook.com/me', [
            'access_token' => $accessToken,
            'fields' => 'id,name,email,picture'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Invalid Facebook access token');
        }
        
        $facebookUser = $response->json();
        
        return $this->handleSocialUser([
            'provider' => 'facebook',
            'provider_id' => $facebookUser['id'],
            'name' => $facebookUser['name'],
            'email' => $facebookUser['email'] ?? null,
            'avatar' => $facebookUser['picture']['data']['url'] ?? null,
        ], $data);
    }

    /**
     * Authenticate with Apple
     */
    public function authenticateWithApple(array $data): array
    {
        $identityToken = $data['identity_token'];
        
        // Decode Apple identity token (simplified - in production use proper JWT verification)
        $tokenParts = explode('.', $identityToken);
        if (count($tokenParts) !== 3) {
            throw new \Exception('Invalid Apple identity token format');
        }
        
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        
        if (!$payload) {
            throw new \Exception('Invalid Apple identity token');
        }
        
        return $this->handleSocialUser([
            'provider' => 'apple',
            'provider_id' => $payload['sub'],
            'name' => $payload['name'] ?? 'Apple User',
            'email' => $payload['email'] ?? null,
            'avatar' => null,
        ], $data);
    }

    /**
     * Handle social user authentication
     */
    private function handleSocialUser(array $socialUser, array $requestData): array
    {
        // Check if user exists by email or provider ID
        $user = User::where('email', $socialUser['email'])
                   ->orWhere('social_provider_id', $socialUser['provider_id'])
                   ->first();
        
        $isNewUser = false;
        
        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $socialUser['name'],
                'email' => $socialUser['email'],
                'mobile' => null, // Will be collected later if needed
                'password' => null, // No password for social login
                'gender' => null, // Will be collected during profile completion
                'dob' => null, // Will be collected during profile completion
                'registration_type' => $requestData['registration_type'],
                'login_type' => 'social',
                'social_provider' => $socialUser['provider'],
                'social_provider_id' => $socialUser['provider_id'],
                'profile_image' => $socialUser['avatar'],
                'device_type' => $requestData['device_type'] ?? null,
                'device_token' => $requestData['device_token'] ?? null,
                'status' => 'active', // Social users are automatically verified
                'email_verified_at' => now(),
            ]);
            
            $isNewUser = true;
        } else {
            // Update existing user's social info and device info
            $user->update([
                'social_provider' => $socialUser['provider'],
                'social_provider_id' => $socialUser['provider_id'],
                'device_type' => $requestData['device_type'] ?? $user->device_type,
                'device_token' => $requestData['device_token'] ?? $user->device_token,
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
        }
        
        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
            'is_new_user' => $isNewUser
        ];
    }
}
