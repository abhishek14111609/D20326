<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Otp;
use App\Services\OtpService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing token field storage...\n\n";

try {
    // Create test user
    $user = User::create([
        'name' => 'Token Field Test',
        'email' => 'tokenfield@example.com',
        'password' => bcrypt('password123'),
        'status' => 'pending_verification'
    ]);

    // Create user profile
    $user->profile()->create([
        'name' => 'Token Field Test',
        'mobile' => '+1111111111',
        'gender' => 'male'
    ]);

    echo "Created test user with ID: {$user->id}\n";

    // Create OTP
    $otp = Otp::create([
        'mobile' => '+1111111111',
        'otp_code' => '1234',
        'type' => 'register',
        'status' => 'pending',
        'expires_at' => now()->addMinutes(5),
        'attempts' => 0,
        'ip_address' => '127.0.0.1'
    ]);

    // Simulate request with all required headers and data
    $request = Request::create('/api/v1/auth/verify-otp', 'POST', [
        'mobile' => '+1111111111',
        'otp' => '1234',
        'type' => 'register',
        'device_type' => 'android',
        'device_token' => 'sample_device_token',
        'registration_type' => 'single'
    ], [], [], [
        'HTTP_DEVICE_TYPE' => 'android',
        'HTTP_DEVICE_TOKEN' => 'sample_device_token',
        'REMOTE_ADDR' => '192.168.1.100',
        'HTTP_USER_AGENT' => 'DUOS Android App v1.0'
    ]);
    
    app()->instance('request', $request);

    // Create OTP service and verify OTP
    $otpService = new OtpService();
    $result = $otpService->verifyOtp('+1111111111', '1234', 'register');

    echo "OTP verified successfully\n";
    echo "Token generated: " . substr($result['token'], 0, 20) . "...\n\n";

    // Check the stored token data
    $latestToken = DB::table('personal_access_tokens')->latest('id')->first();
    
    echo "Personal Access Token Fields:\n";
    echo "- device_type: " . ($latestToken->device_type ?: 'BLANK') . "\n";
    echo "- device_token: " . ($latestToken->device_token ?: 'BLANK') . "\n";
    echo "- ip_address: " . ($latestToken->ip_address ?: 'BLANK') . "\n";
    echo "- user_agent: " . ($latestToken->user_agent ?: 'BLANK') . "\n";
    echo "- login_method: " . ($latestToken->login_method ?: 'BLANK') . "\n";
    echo "- otp_verified_at: " . ($latestToken->otp_verified_at ?: 'BLANK') . "\n";
    echo "- last_used_at: " . ($latestToken->last_used_at ?: 'BLANK') . "\n";
    echo "- expires_at: " . ($latestToken->expires_at ?: 'BLANK') . "\n";
    echo "- user_details: " . ($latestToken->user_details ?: 'BLANK') . "\n";

    // Cleanup
    $user->tokens()->delete();
    $user->customTokens()->delete();
    $user->profile()->delete();
    $user->delete();
    $otp->delete();

    echo "\nTest completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
