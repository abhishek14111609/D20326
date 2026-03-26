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

echo "Testing token storage in both tables...\n";

// Check initial counts
$tokensCount = DB::table('tokens')->count();
$personalTokensCount = DB::table('personal_access_tokens')->count();

echo "Before test:\n";
echo "- Custom tokens table: {$tokensCount}\n";
echo "- Personal access tokens table: {$personalTokensCount}\n\n";

try {
    // Create test user
    $user = User::create([
        'name' => 'Token Test User',
        'email' => 'tokentest@example.com',
        'password' => bcrypt('password123'),
        'status' => 'pending_verification'
    ]);

    // Create user profile
    $user->profile()->create([
        'name' => 'Token Test User',
        'mobile' => '+9876543210',
        'gender' => 'male'
    ]);

    echo "Created test user with ID: {$user->id}\n";

    // Create OTP
    $otp = Otp::create([
        'mobile' => '+9876543210',
        'otp_code' => '1234',
        'type' => 'register',
        'status' => 'pending',
        'expires_at' => now()->addMinutes(5),
        'attempts' => 0,
        'ip_address' => '127.0.0.1'
    ]);

    echo "Created OTP with ID: {$otp->id}\n";

    // Simulate request with headers
    $request = Request::create('/test', 'POST', [], [], [], [
        'HTTP_DEVICE_TYPE' => 'android',
        'HTTP_DEVICE_TOKEN' => 'test_device_token_123',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_USER_AGENT' => 'Test User Agent'
    ]);
    app()->instance('request', $request);

    // Create OTP service and verify OTP
    $otpService = new OtpService();
    $result = $otpService->verifyOtp('+9876543210', '1234', 'register');

    echo "OTP verified successfully\n";
    echo "Token generated: " . substr($result['token'], 0, 20) . "...\n";

    // Check counts after token creation
    $newTokensCount = DB::table('tokens')->count();
    $newPersonalTokensCount = DB::table('personal_access_tokens')->count();

    echo "\nAfter test:\n";
    echo "- Custom tokens table: {$newTokensCount} (+" . ($newTokensCount - $tokensCount) . ")\n";
    echo "- Personal access tokens table: {$newPersonalTokensCount} (+" . ($newPersonalTokensCount - $personalTokensCount) . ")\n";

    // Show token details
    $latestCustomToken = DB::table('tokens')->latest('id')->first();
    if ($latestCustomToken) {
        echo "\nLatest custom token details:\n";
        echo "- User ID: {$latestCustomToken->user_id}\n";
        echo "- Device Type: {$latestCustomToken->device_type}\n";
        echo "- Device ID: {$latestCustomToken->device_id}\n";
        echo "- Expires At: {$latestCustomToken->expires_at}\n";
    }

    $latestPersonalToken = DB::table('personal_access_tokens')->latest('id')->first();
    if ($latestPersonalToken) {
        echo "\nLatest personal access token details:\n";
        echo "- Name: {$latestPersonalToken->name}\n";
        echo "- Device Type: {$latestPersonalToken->device_type}\n";
        echo "- IP Address: {$latestPersonalToken->ip_address}\n";
        echo "- Login Method: {$latestPersonalToken->login_method}\n";
    }

    // Cleanup
    $user->tokens()->delete();
    $user->customTokens()->delete();
    $user->profile()->delete();
    $user->delete();
    $otp->delete();

    echo "\nTest completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
