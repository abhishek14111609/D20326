<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\SwipeController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\GiftController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\AudioCallController;
use App\Http\Controllers\Api\VideoCallController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RtmController;
use App\Http\Controllers\Api\AgoraController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AgoraTokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API Routes (No Authentication Required)
    
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/duo-register', [AuthController::class, 'duoRegister']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/reactivate-profile', [UserController::class, 'reactivateProfile']);
    });
    
    // Social Authentication Routes
    Route::prefix('social')->group(function () {
        Route::post('/google', [SocialAuthController::class, 'google']);
        Route::post('/facebook', [SocialAuthController::class, 'facebook']);
        Route::post('/apple', [SocialAuthController::class, 'apple']);
    });
    
    // Agora Token Routes (Public)
    Route::prefix('agora')->group(function () {
        //Route::post('/token', [AgoraController::class, 'generateRtmToken']);
        Route::post('/rtc-token', [AgoraController::class, 'generateRtcToken']);
        Route::get('/config', [AgoraController::class, 'getConfig']);
    });

	Route::post('/agora/token', [AgoraController::class, 'generateToken']);
    Route::post('/agora/check', [AgoraController::class, 'validateToken']);
    
    // Test endpoint to debug request data
    Route::post('/test-request', function(Request $request) {
        // Log all request data
        \Log::info('=== TEST REQUEST DEBUGGING ===');
        \Log::info('Headers:', $request->headers->all());
        \Log::info('Content Type: ' . $request->header('Content-Type'));
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Request All Input:', $request->all());
        \Log::info('Request JSON: ', $request->json()->all());
        \Log::info('Raw Content: ' . $request->getContent());
        \Log::info('Is JSON: ' . ($request->isJson() ? 'true' : 'false'));
        \Log::info('Is Form Data: ' . ($request->is('*') ? 'true' : 'false'));
        \Log::info('Is Multipart: ' . ($request->isMultipart() ? 'true' : 'false'));
        \Log::info('================================');
        
        return response()->json([
            'success' => true,
            'headers' => array_map(function($header) {
                return is_array($header) ? implode(', ', $header) : $header;
            }, $request->headers->all()),
            'input' => $request->all(),
            'json' => $request->json()->all(),
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'raw_content' => $request->getContent(),
        ]);
    });

    // Protected API Routes (Authentication Required)
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile Routes
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'getProfile']);
            // Keep the old endpoint for backward compatibility
            Route::any('/profile/update', [UserController::class, 'updateProfile']);
            // New endpoints for single and duo profiles
            Route::any('/single-profile/update', [UserController::class, 'updateSingleProfile']);
            Route::any('/duo-profile/update', [UserController::class, 'updateDuoProfile']);
            Route::get('/profile', [UserController::class, 'profile']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::post('/upload-avatar', [UserController::class, 'uploadAvatar']);
            Route::delete('/delete-account', [UserController::class, 'deleteAccount']);
			
			Route::get('/id/{id}', [UserController::class, 'getuser']);
        });

        // 1. Configuration/Settings
        Route::prefix('settings')->group(function () {
            // Public settings (no auth required)
            Route::get('/public', [SettingController::class, 'publicSettings']);
            Route::get('/group/{group}', [SettingController::class, 'getByGroup']);
            Route::get('/{key}', [SettingController::class, 'show']);
            Route::get('/', [SettingController::class, 'index']);
            
            // Protected admin-only routes (require admin middleware)
            Route::middleware('admin')->group(function () {
                Route::post('/', [SettingController::class, 'store']);
                Route::put('/{key}', [SettingController::class, 'update']);
                Route::delete('/{key}', [SettingController::class, 'destroy']);
            });
        });

        // 2. Home/Dashboard
        Route::prefix('home')->group(function () {
            Route::get('/', [HomeController::class, 'index']);
            Route::get('/suggestions', [HomeController::class, 'suggestions']);
            Route::get('/nearby', [HomeController::class, 'nearbyUsers']);
            Route::get('/recent-activity', [HomeController::class, 'recentActivity']);
        });

        // 3. Swipe Match & 4. Like/Dislike
        Route::prefix('swipe')->group(function () {
            Route::get('/profiles', [SwipeController::class, 'getProfiles']);
            Route::post('/like/{user}', [SwipeController::class, 'likeProfile']);
            Route::post('/dislike/{user}', [SwipeController::class, 'dislikeProfile']);
            Route::post('/super-like/{user}', [SwipeController::class, 'superLikeProfile']);
            Route::get('/matches', [SwipeController::class, 'getMatches']);
            Route::get('/match/{user}', [SwipeController::class, 'getMatch']);
            Route::get('/likes-you', [SwipeController::class, 'likesYou']);
        });

        // 5. Chat (Legacy)
        Route::prefix('chat')->group(function () {
            Route::get('/conversations', [ChatController::class, 'conversations']);
            Route::get('/{chatId}', [ChatController::class, 'getChatDetails']);
            Route::get('/conversation/{user}', [ChatController::class, 'getMessages']);
            Route::post('/send/{user}', [ChatController::class, 'sendMessage']);
            Route::post('/send-image/{user}', [ChatController::class, 'sendImage']);
            Route::delete('/message/{message}', [ChatController::class, 'deleteMessage']);
            Route::post('/typing/{user}', [ChatController::class, 'typing']);
            Route::post('/read/{user}', [ChatController::class, 'markAsRead']);
        });

        // 5.1. Real-time Messaging (RTM)
        Route::prefix('rtm')->group(function () {
            Route::get('/config', [RtmController::class, 'getConfig']);
            Route::post('/message', [RtmController::class, 'sendMessage']);
            Route::get('/conversations', [RtmController::class, 'getConversations']);
            Route::get('/conversations/{userId}/messages', [RtmController::class, 'getConversationMessages']);
            Route::post('/typing', [RtmController::class, 'sendTypingIndicator']);
            Route::post('/read-receipt', [RtmController::class, 'sendReadReceipt']);
            Route::get('/offline-messages', [RtmController::class, 'getOfflineMessages']);
            Route::post('/user-status', [RtmController::class, 'updateUserStatus']);
            Route::get('/user-status/{userId}', [RtmController::class, 'getUserStatus']);
            Route::post('/refresh-token', [RtmController::class, 'refreshToken']);
        });
        
        // Agora Token Refresh (Authenticated)
        Route::post('/agora/refresh-token', [AgoraController::class, 'refreshRtmToken']);
		Route::post('/agora/chat-token', [AgoraController::class, 'generateChatToken']);

        // 5.2. Enhanced Instant Messaging (WhatsApp-like)
        Route::prefix('instant')->group(function () {
            Route::post('/send', [App\Http\Controllers\Api\EnhancedRtmController::class, 'sendInstantMessage']);
            Route::get('/messages/{userId}', [App\Http\Controllers\Api\EnhancedRtmController::class, 'getInstantMessages']);
            Route::post('/status', [App\Http\Controllers\Api\EnhancedRtmController::class, 'updateMessageStatus']);
            Route::post('/typing', [App\Http\Controllers\Api\EnhancedRtmController::class, 'sendTypingIndicator']);
            Route::get('/presence/{userId}', [App\Http\Controllers\Api\EnhancedRtmController::class, 'getUserPresence']);
        });

        // 6. Gifts & Video Calls
        Route::prefix('gifts')->group(function () {
            Route::get('/', [GiftController::class, 'index']);
            Route::post('/send/{user}', [GiftController::class, 'sendGift']);
            Route::post('/video-call/initiate/{user}', [GiftController::class, 'initiateVideoCall']);
            Route::post('/video-call/accept/{call}', [GiftController::class, 'acceptVideoCall']);
            Route::post('/video-call/end/{call}', [GiftController::class, 'endVideoCall']);
            Route::get('/history', [GiftController::class, 'giftHistory']);
    		Route::post('/confirm-payment', [GiftController::class, 'confirmPayment']);
			
			Route::get('/wallet/{userId}', [GiftController::class, 'fetchWallet']);
        });
		

        // 7. Membership & In-App Purchases
        Route::prefix('membership')->group(function () {
            Route::get('/plans', [MembershipController::class, 'plans']);
            Route::post('/subscribe', [MembershipController::class, 'subscribe']);
            Route::get('/current', [MembershipController::class, 'currentMembership']);
            Route::get('/history', [MembershipController::class, 'history']);
            Route::post('/cancel', [MembershipController::class, 'cancelSubscription']);
            Route::get('/benefits', [MembershipController::class, 'membershipBenefits']);
        });

        // 8. Leaderboard
        Route::prefix('leaderboard')->group(function () {
            Route::get('/', [LeaderboardController::class, 'index']);
            Route::get('/global', [LeaderboardController::class, 'global']);
            Route::get('/friends', [LeaderboardController::class, 'friends']);
            Route::get('/monthly', [LeaderboardController::class, 'monthly']);
            Route::get('/all-time', [LeaderboardController::class, 'allTime']);
        });

        // 9. Competitions & Challenges
        Route::prefix('competitions')->group(function () {
            Route::get('/', [CompetitionController::class, 'index']);
            Route::get('/active', [CompetitionController::class, 'active']);
            Route::get('/{competition}', [CompetitionController::class, 'show']);
            Route::post('/{competition}/join', [CompetitionController::class, 'join']);
            Route::get('/{competition}/leaderboard', [CompetitionController::class, 'leaderboard']);
            Route::get('/{competition}/participants', [CompetitionController::class, 'participants']);
       		
			Route::post('/quiz/{quizId}/answer', [\App\Http\Controllers\Api\QuizController::class, 'submitAllAnswers']);

            // Join a competition
            //Route::post('/{competitionId}/join', [\App\Http\Controllers\Api\QuizController::class, 'joinCompetition']);
            
			
			//Route::prefix('{competitionId}/quiz')->group(function () {
			//Route::get('/', [\App\Http\Controllers\Api\QuizController::class, 'index']);
			// Start a quiz
			//Route::post('/start', [\App\Http\Controllers\Api\QuizController::class, 'startQuiz']);
			
			//Route::post('/{quizId}/end', [\App\Http\Controllers\Api\QuizController::class, 'endQuiz']);
			
			//Route::post('/{quizId}/complete', [\App\Http\Controllers\Api\QuizController::class, 'completeQuiz']);

			// Submit an answer
			//Route::post('/questions/{questionId}/answer', [\App\Http\Controllers\Api\QuizController::class, 'submitAnswer']);

			// Get current quiz state
			//Route::get('/{quizId}/state', [\App\Http\Controllers\Api\QuizController::class, 'getQuizState']);
                
                // Deprecate the old route
                //Route::get('/state', function() {
                    //return response()->json([
                       // 'status' => 'error',
                      //  'message' => 'This endpoint is deprecated. Please use /{quizId}/state instead.'
                   // ], 410);
               // });
           //});
        });

        // 10. Challenges - Public Routes
    Route::prefix('challenges')->group(function () {
        Route::get('/', [ChallengeController::class, 'index']);
        Route::get('/active', [ChallengeController::class, 'active']);
        Route::get('/featured', [ChallengeController::class, 'featured']);
        Route::get('/{challenge}', [ChallengeController::class, 'show']);
        
        Route::post('/{challenge}/join', [\App\Http\Controllers\Api\ChallengeParticipationController::class, 'joinChallenge']);
        
        // Get challenge participants (public)
        Route::get('/{challenge}/participants', [\App\Http\Controllers\Api\ChallengeParticipationController::class, 'getParticipants']);
        
        // Get user's challenges
        Route::get('/my-challenges', [\App\Http\Controllers\Api\ChallengeParticipationController::class, 'getUserChallenges']);
        
        // Update participation status
        Route::put('/{challenge}/status', [\App\Http\Controllers\Api\ChallengeParticipationController::class, 'updateStatus']);

        // Leave a challenge
        Route::delete('/{challenge}/leave', [\App\Http\Controllers\Api\ChallengeParticipationController::class, 'leaveChallenge']);

    });

    // Audio Call Routes
    Route::prefix('calls/audio')->middleware('auth:sanctum')->group(function () {
        // Clean up any stale calls
        Route::post('/cleanup', [AudioCallController::class, 'cleanupStaleCalls']);
        
        // Initiate a new audio call
        Route::post('/initiate', [AudioCallController::class, 'initiate']);
        
        // Get call details
        Route::get('/{callId}', [AudioCallController::class, 'getCallDetails']);
        
        // Accept an incoming call
        Route::post('/{callId}/accept', [AudioCallController::class, 'acceptCall'])
            ->middleware('throttle:60,1');
            
        // Reject an incoming call
        Route::post('/{callId}/reject', [AudioCallController::class, 'rejectCall'])
            ->middleware('throttle:60,1');
            
        // End an ongoing call
        Route::post('/{callId}/end', [AudioCallController::class, 'endCall']);
        
        // Mute audio in call
        Route::post('/{callId}/mute', [AudioCallController::class, 'muteCall']);
        
        // Unmute audio in call
        Route::post('/{callId}/unmute', [AudioCallController::class, 'unmuteCall']);
        
        // Get Agora token for audio call
        Route::get('/{callId}/agora-token', [AudioCallController::class, 'getAgoraToken']);
        
        // Get call statistics
        Route::get('/{callId}/stats', [AudioCallController::class, 'getCallStats']);
    });

    // Video Call Routes
    Route::prefix('calls/video')->middleware('auth:sanctum')->group(function () {
        // Initiate a new video call
        Route::post('/initiate', [VideoCallController::class, 'initiate']);
        
        // Accept an incoming video call
        Route::post('/{callId}/accept', [VideoCallController::class, 'accept'])
            ->where('callId', '[a-zA-Z0-9_-]+');
        
        // End an ongoing video call
        Route::post('/{callId}/end', [VideoCallController::class, 'end'])
            ->where('callId', '[a-zA-Z0-9_-]+');
        
        // Mute audio in video call
        Route::post('/{callId}/mute', [VideoCallController::class, 'mute'])
            ->where('callId', '[a-zA-Z0-9_-]+');
        
        // Unmute audio in video call
        Route::post('/{callId}/unmute', [VideoCallController::class, 'unmute'])
            ->where('callId', '[a-zA-Z0-9_-]+');
        
        // Toggle video on/off during a call
        Route::post('/{callId}/toggle-video', [VideoCallController::class, 'toggleVideo'])
            ->where('callId', '[a-zA-Z0-9_-]+');
        
        // Get Agora token for a video call
        // Route::get('/{callId}/agora-token', [VideoCallController::class, 'getAgoraToken'])
        //     ->where('callId', '[a-zA-Z0-9_-]+');
        Route::get('/{call_id}/agora-token', [VideoCallController::class, 'getAgoraToken']);
    });

        // 11. Report & Block Users
        Route::prefix('report')->group(function () {
            Route::post('/user/{user}', [ReportController::class, 'reportUser']);
            Route::post('/block/{user}', [ReportController::class, 'blockUser']);
            Route::get('/blocked-users', [ReportController::class, 'blockedUsers']);
            Route::post('/unblock/{user}', [ReportController::class, 'unblockUser']);
            Route::get('/reasons', [ReportController::class, 'reportReasons']);
        });

        // Delete Profile Route
        Route::delete('/delete-profile', [UserController::class, 'deleteProfile']);

        // Auth Routes for logged-in users
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
            Route::get('/me', [AuthController::class, 'me']);
        });
        
        // Duo/Partner Routes
        Route::prefix('duo')->group(function () {
            Route::get('/partner', [UserController::class, 'getPartner']);
            Route::post('/invite-partner', [UserController::class, 'invitePartner']);
            Route::post('/accept-invitation', [UserController::class, 'acceptInvitation']);
            Route::post('/remove-partner', [UserController::class, 'removePartner']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{notification}', [NotificationController::class, 'destroy']);
            Route::delete('/', [NotificationController::class, 'clearAll']);
            
            // Test endpoint - can be removed in production
            Route::post('/test', [NotificationController::class, 'testNotification']);
        });
		
		Route::prefix('payments')->group(function () {
            Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
            Route::get('/status/{paymentIntentId}', [PaymentController::class, 'getPaymentStatus']);
        });
		
		
		// FCM Token Management
        Route::prefix('fcm')->middleware('auth:api')->group(function () {
            Route::post('/tokens/register', [\App\Http\Controllers\Api\FcmTokenController::class, 'registerToken']);
            Route::post('/tokens/remove', [\App\Http\Controllers\Api\FcmTokenController::class, 'removeToken']);
            Route::delete('/tokens/clear', [\App\Http\Controllers\Api\FcmTokenController::class, 'clearTokens']);
            
            // Test notification endpoint
            Route::post('/test-notification', [\App\Http\Controllers\Api\FcmTokenController::class, 'sendTestNotification']);
			Route::post('/send-fcm', [App\Http\Controllers\Api\FcmController::class, 'send']);
        });
		
    }); // End of auth:sanctum middleware

Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])
    ->name('stripe.webhook');


// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'DUOS API is running',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
