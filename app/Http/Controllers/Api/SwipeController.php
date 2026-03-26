<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserCollection;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\SwipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Jobs\SendPushNotificationJob;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
/**
 * @OA\Tag(
 *     name="Swipe",
 *     description="Swipe functionality for matching with other users"
 * )
 */
class SwipeController extends Controller
{
    protected $swipeService;

    public function __construct(SwipeService $swipeService)
    {
        $this->swipeService = $swipeService;
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/swipe/profiles",
     *     summary="Get profiles for swiping",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of profiles to swipe on",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
public function getProfiles(Request $request): JsonResponse
{
    $page = $request->query('page', 1);
    $perPage = $request->query('per_page', 10);

    $profiles = $this->swipeService->getProfilesForUser(
        auth()->id(),
        $page,
        $perPage
    );

    // 🔒 SAFETY CHECK
    if (!($profiles instanceof \Illuminate\Pagination\LengthAwarePaginator)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid response from service'
        ], 500);
    }

    return response()->json([
        'status' => 'success',
        'data' => new UserCollection($profiles->items()),
        'pagination' => [
            'total' => $profiles->total(),
            'count' => $profiles->count(),
            'total_pages' => $profiles->lastPage(),
            'current_page' => $profiles->currentPage(),
            'from' => $profiles->firstItem(),
            'to' => $profiles->lastItem(),
            'per_page' => $profiles->perPage(),
        ]
    ]);
}


    /**
     * @OA\Post(
     *     path="/api/v1/swipe/like/{user}",
     *     summary="Like a user's profile",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to like",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile liked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile liked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_match", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
public function likeProfile($userId): JsonResponse
{
    $user = auth()->user();
    $result = $this->swipeService->likeUser($user->id, $userId);

    $receiver = User::find($userId);
    if (!$receiver) {
        return response()->json(['status' => 'error', 'message' => 'User not found']);
    }

    // MATCH or JUST LIKE
try {
    $sender = auth()->user();
    $receiver = User::find($userId);

    if (!$receiver) {
        throw new \Exception("Receiver not found");
    }

    // Set message
    if ($result['is_match']) {
        $title = "It's a Match!";
        $body  = $sender->name . " also liked you!";
        $type  = "match";
    } else {
        $title = "Someone liked you!";
        $body  = $sender->name . " liked your profile.";
        $type  = "like";
    }

    // 1️⃣ DB Notification (SAME FORMAT)
    $notification = Notification::create([
        'user_id' => $receiver->id,
        'type' => Notification::TYPE_SYSTEM,
        'message' => $body,
        'data' => [
            'swiper_id' => $sender->id,
            'is_match' => $result['is_match'],
            'notification_type' => $type
        ]
    ]);

    // 2️⃣ Send FCM
    $this->sendLikeNotification($receiver, $notification, $title, $body, $type);

} catch (\Exception $e) {
    Log::error("Like/Match error", [
        "error" => $e->getMessage()
    ]);
}


    return response()->json([
        'status' => 'success',
        'message' => $result['is_match']
            ? 'You are both liked! You can start the chat.'
            : 'Profile liked successfully',
        'data' => [
            'is_match' => $result['is_match']
        ]
    ]);
}

	
protected function sendLikeNotification(User $receiver, Notification $notification, $title, $body, $type)
{
    try {
        $fcmToken = $receiver->fcm_token
            ?? $receiver->fcmTokens()->latest()->first()?->token;

        if (!$fcmToken) {
            Log::info("No FCM token for like", ['user_id' => $receiver->id]);
            return;
        }

        $fcmService = app(\App\Services\FcmService::class);

        // 🟢 SAME FORMAT (Profile Update जैसा)
        $payload = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ],
                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "sound" => "default"
                    ],
                ],
                "apns" => [
                    "headers" => ["apns-priority" => "10"],
                    "payload" => [
                        "aps" => ["sound" => "default"]
                    ]
                ],
                "data" => [
                    "type" => $type,
                    "notification_id" => (string)$notification->id,
                    "swiper_id" => (string)$notification->data['swiper_id'],
                    "is_match" => (string)$notification->data['is_match'],
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ]
            ]
        ];

        // Final call
        $fcmService->sendNotification(
            $fcmToken,
            $title,
            $body,
            $payload["message"]["data"]
        );

        Log::info("LIKE/MATCH FCM SENT", [
            "receiver_id" => $receiver->id
        ]);

    } catch (\Exception $e) {
        Log::error("Like FCM failed", ['error' => $e->getMessage()]);
    }
}

    /**
     * @OA\Post(
     *     path="/api/v1/swipe/dislike/{user}",
     *     summary="Dislike a user's profile",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to dislike",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile disliked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile disliked")
     *         )
     *     )
     * )
     */
    public function dislikeProfile($userId): JsonResponse
    {
        $this->swipeService->dislikeUser(auth()->id(), $userId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Profile disliked'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/swipe/super-like/{user}",
     *     summary="Super like a user's profile",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to super like",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile super liked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Super like sent successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_match", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function superLikeProfile($userId): JsonResponse
    {
        $result = $this->swipeService->superLikeUser(auth()->id(), $userId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Super like sent successfully',
            'data' => [
                'is_match' => $result['is_match']
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/swipe/matches",
     *     summary="Get user's matches",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of matched users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function getMatches(Request $request): JsonResponse
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
    
        // Fetch paginated matches
        $matches = $this->swipeService->getUserMatches(auth()->id(), $page, $perPage);
    
        return response()->json([
            'status' => 'success',
            'data' => new UserCollection($matches),
            'pagination' => [
                'total' => $matches->total(),
                'count' => $matches->count(),
                'total_pages' => ceil($matches->total() / $perPage),
                'current_page' => $matches->currentPage(),
                'from' => $matches->firstItem() ?? 0,
                'last_page' => $matches->lastPage(),
                'per_page' => $matches->perPage(),
                'to' => $matches->lastItem() ?? 0,
            ]
        ]);
    }
    

    /**
     * @OA\Get(
     *     path="/api/v1/swipe/match/{user}",
     *     summary="Get match details with a specific user",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to check match with",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_match", type="boolean", example=true),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not a match"
     *     )
     * )
     */
    public function getMatch($userId): JsonResponse
    {
        $currentUser = auth()->user();
        
        // Check if the other user exists
        $user = User::with('profile')->find($userId);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        // Check if there's a mutual like (match)
        $isMatch = $this->swipeService->checkIfMatchExists($currentUser->id, $userId);
        
        if (!$isMatch) {
            return response()->json([
                'status' => 'error',
                'message' => 'No match found with this user'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'is_match' => true,
                'user' => new UserResource($user)
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/swipe/likes-you",
     *     summary="Get users who liked the authenticated user",
     *     tags={"Swipe"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users who liked the authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
   public function likesYou(Request $request): JsonResponse
	{
		$page = $request->query('page', 1);
		$perPage = $request->query('per_page', 10);

		// Get likes for the user
		$likes = $this->swipeService->getLikesForUser(auth()->id(), $page, $perPage);
		
		// Pagination structure
		$pagination = [
			'total' => $likes->total(),
			'count' => $likes->count(),
			'total_pages' => ceil($likes->total() / $perPage),
			'current_page' => $likes->currentPage(),
			'from' => $likes->firstItem() ?? 0,
			'last_page' => $likes->lastPage(),
			'per_page' => $likes->perPage(),
			'to' => $likes->lastItem() ?? 0,
		];
	
		return response()->json([
			'status' => 'success',
			'data' => new UserCollection($likes),
			'pagination' => $pagination
		]);
	}

}
