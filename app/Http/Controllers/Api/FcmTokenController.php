<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\FcmService;

class FcmTokenController extends Controller
{
    /**
     * Register token
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'device_os'   => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        try {
            $token = $user->addFcmToken(
                $request->token,
                $request->device_name,
                $request->device_os,
                $request->app_version
            );

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
                'token'   => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a single token
     */
    public function removeToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        $deleted = $user->removeFcmToken($request->token);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'FCM token removed successfully'
                : 'FCM token not found'
        ], $deleted ? 200 : 404);
    }

    /**
     * Clear all tokens
     */
    public function clearTokens()
    {
        /** @var User $user */
        $user = Auth::user();
        $deleted = $user->clearFcmTokens();

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'All FCM tokens removed successfully'
                : 'No tokens found to remove'
        ]);
    }

    /**
     * SEND TEST NOTIFICATION
     */
    public function sendTestNotification(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'No FCM tokens found for this user',
                'hint'    => 'Register a token using /api/fcm/tokens/register'
            ], 400);
        }

        // Notification content
        $title = 'Test Notification';
        $body  = 'This is a test notification from ' . config('app.name');

        // Key/value STRING data
        $data = [
            'type'          => 'test',
            'test_id'       => 'test_' . time(),
            'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            $fcmService = app(FcmService::class);

            // Send notification
            $results = $fcmService->sendToDevices($tokens, $title, $body, $data);

            // Count success/fail
            $successCount = collect($results)->where('success', true)->count();
            $failedCount  = count($tokens) - $successCount;

            return response()->json([
                'success'       => $failedCount === 0,
                'message'       => $failedCount === 0
                    ? "Notification sent successfully"
                    : "Sent to {$successCount}, failed for {$failedCount}",
                'results'       => $results,
                'devices'       => $tokens,
                'success_count' => $successCount,
                'failed_count'  => $failedCount
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error sending notification',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
