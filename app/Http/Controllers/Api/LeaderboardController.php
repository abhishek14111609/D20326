<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LeaderboardCollection;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Leaderboard",
 *     description="Leaderboard related operations"
 * )
 */
class LeaderboardController extends Controller
{
    protected $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard",
     *     summary="Get leaderboard rankings",
     *     tags={"Leaderboard"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of leaderboard (daily, weekly, monthly, all_time)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "all_time"}, default="weekly")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Leaderboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Leaderboard")),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", nullable=true, example=null),
     *                     @OA\Property(property="last_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="to", type="integer", nullable=true, example=null),
     *                     @OA\Property(property="total", type="integer", example=0)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
	{
		$type = $request->input('type', 'weekly');
		$perPage = (int) $request->input('per_page', 10);
		$page = (int) $request->input('page', 1);

		$user = auth()->user();

		$total = User::where('status', 'active')->count();
		$lastPage = max(1, ceil($total / $perPage));
		$offset = ($page - 1) * $perPage;

		$leaderboard = $this->leaderboardService->getLeaderboard($type, $perPage, $offset);

		$from = $total > 0 ? $offset + 1 : null;
		$to = $total > 0 ? min($offset + $perPage, $total) : null;

		// 🔥 fetch current user leaderboard info
		$currentUser = $user
			? $this->leaderboardService->getUserLeaderboardData($user->id, $type)
			: null;

		return response()->json([
			'status' => 'success',
			'current_user' => $currentUser,
			'data' => $leaderboard,
			'pagination' => [
				'total' => $total,
				'count' => $total,
				'total_pages' => ceil($total / $perPage),
				'current_page' => $page,
				'from' => $from,
				'last_page' => $lastPage,
				'per_page' => $perPage,
				'to' => $to,
			]
		]);
	}

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/me",
     *     summary="Get current user's leaderboard position",
     *     tags={"Leaderboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of leaderboard (daily, weekly, monthly, all_time)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "all_time"}, default="weekly")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User's leaderboard position retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="position", type="integer", example=5),
     *                 @OA\Property(property="score", type="number", format="float", example=1250.5),
     *                 @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *                 @OA\Property(property="top_percentage", type="number", format="float", example=95.5)
     *             )
     *         )
     *     )
     * )
     */
    public function myPosition(Request $request): JsonResponse
    {
        $type = $request->input('type', 'weekly');
        $userId = auth()->id();
        
        $position = $this->leaderboardService->getUserPosition($userId, $type);
        
        if (!$position) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not determine your position on the leaderboard.'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $position
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/around-me",
     *     summary="Get leaderboard positions around the current user",
     *     tags={"Leaderboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of leaderboard (daily, weekly, monthly, all_time)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "all_time"}, default="weekly")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of positions to return (total, including the user's position)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=3, maximum=21, default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Leaderboard positions around user retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_position", type="integer", example=15),
     *                 @OA\Property(property="entries", type="array",
     *                     @OA\Items(ref="#/components/schemas/LeaderboardEntry")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function aroundMe(Request $request): JsonResponse
    {
        $type = $request->input('type', 'weekly');
        $limit = min(max((int) $request->input('limit', 5), 3), 21); // Ensure odd number between 3 and 21
        $userId = auth()->id();
        
        $aroundMe = $this->leaderboardService->getPositionsAroundUser($userId, $type, $limit);
        
        return response()->json([
            'status' => 'success',
            'data' => $aroundMe
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/rewards",
     *     summary="Get leaderboard rewards information",
     *     tags={"Leaderboard"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of leaderboard (daily, weekly, monthly, all_time)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "all_time"}, default="weekly")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Leaderboard rewards retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="type", type="string", example="weekly"),
     *                 @OA\Property(property="ends_in", type="integer", example=123456),
     *                 @OA\Property(property="rewards", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="position_start", type="integer", example=1),
     *                         @OA\Property(property="position_end", type="integer", example=3),
     *                         @OA\Property(property="rewards", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="type", type="string", example="coins"),
     *                                 @OA\Property(property="amount", type="integer", example=1000)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function rewards(Request $request): JsonResponse
    {
        $type = $request->input('type', 'weekly');
        $rewards = $this->leaderboardService->getLeaderboardRewards($type);
        
        return response()->json([
            'status' => 'success',
            'data' => $rewards
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/global",
     *     summary="Get global leaderboard",
     *     tags={"Leaderboard"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top users to return",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Global leaderboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="position", type="integer", example=1),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="user_name", type="string", example="johndoe"),
     *                         @OA\Property(property="avatar", type="string", example="path/to/avatar.jpg"),
     *                         @OA\Property(property="is_verified", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="score", type="integer", example=1500)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function global(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        
        // Get total count for pagination
        $total = User::where('status', 'active')->count();
        $lastPage = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        
        // Get paginated results
        $leaderboard = $this->leaderboardService->getLeaderboard('all_time', $perPage, $offset);
       
        // Calculate from and to
        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;
        
        return response()->json([
            'status' => 'success',
           
            'data' => $leaderboard->map(function ($user) use ($offset) {
                return [
                    'position' => $user->rank,
                    'user' => [
                        'id' => $user->user_id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'is_verified' => (bool) ($user->is_verified ?? false),
                    ],
                    'score' => (int) $user->points
                ];
            }),
            'pagination' => [
                'total' => $total,
                'count' => $total,
                'total_pages' => ceil($total / $perPage),
                'current_page' => $page,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
            ]
           
        ]);
    }
    

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/friends",
     *     summary="Get friends leaderboard",
     *     tags={"Leaderboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of leaderboard (daily, weekly, monthly, all_time)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "all_time"}, default="weekly")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top friends to return",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Friends leaderboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="position", type="integer", example=1),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="user_name", type="string", example="johndoe"),
     *                         @OA\Property(property="avatar", type="string", example="path/to/avatar.jpg"),
     *                         @OA\Property(property="is_verified", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="score", type="integer", example=1500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function friends(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->input('type', 'weekly');
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        
        // Get the user's friends from matched swipes
        $swipedFriends = $user->sentSwipes()
            ->where('matched', true)
            ->pluck('swiped_id');
            
        $receivedFriends = $user->receivedSwipes()
            ->where('matched', true)
            ->pluck('swiper_id');
            
        $friendsIds = $swipedFriends->merge($receivedFriends)->unique()->toArray();
        $friendsIds[] = $user->id; // Include current user in the leaderboard
        $total = count($friendsIds);
        $lastPage = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;
        
        if (count($friendsIds) <= 1) { // Only current user or no friends
            return response()->json([
                'status' => 'success',
                
                'data' => [],
                'pagination' => [
                'total' => $total,
                'count' => $total,
                'total_pages' => ceil($total / $perPage),
                'current_page' => $page,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
                'total' => $total
                ]
             
            ]);
        }
        
        // Get total count for pagination
        // $total = count($friendsIds);
        // $lastPage = max(1, ceil($total / $perPage));
        // $offset = ($page - 1) * $perPage;
        
        // Get paginated friend IDs
        $paginatedFriendIds = array_slice($friendsIds, $offset, $perPage);
        
        // Get leaderboard data for friends with pagination
        $leaderboard = $this->leaderboardService->getLeaderboard($type, $perPage, $offset, $paginatedFriendIds);
        
        // Calculate from and to
        // $from = $total > 0 ? $offset + 1 : null;
        // $to = $total > 0 ? min($offset + $perPage, $total) : null;
        
        return response()->json([
            'status' => 'success',
           
                'data' => $leaderboard->map(function ($user) use ($offset) {
                    return [
                        'position' => $user->rank,
                        'user' => [
                            'id' => $user->user_id,
                            'name' => $user->name,
                            'avatar' => $user->avatar,
                            'is_verified' => (bool) ($user->is_verified ?? false),
                        ],
                        'score' => (int) $user->points
                    ];
                }),
               'pagination' => [
                'total' => $total,
                'count' => $total,
                'total_pages' => ceil($total / $perPage),
                'current_page' => $page,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
            ]
          
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/monthly",
     *     summary="Get monthly leaderboard",
     *     tags={"Leaderboard"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top users to return",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Monthly leaderboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="position", type="integer", example=1),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="user_name", type="string", example="johndoe"),
     *                         @OA\Property(property="avatar", type="string", example="path/to/avatar.jpg"),
     *                         @OA\Property(property="is_verified", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="score", type="integer", example=1500)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function monthly(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
    
        // Get full leaderboard data
        $allLeaderboard = $this->leaderboardService->getLeaderboard('monthly');
    
        $total = $allLeaderboard->count();
        $lastPage = max(1, ceil($total / $perPage));
        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;
    
        // Slice the leaderboard manually
        $paginatedLeaderboard = $allLeaderboard->slice($offset, $perPage);
    
        return response()->json([
            'status' => 'success',
            'data' => $paginatedLeaderboard->map(function ($user) {
                return [
                    'position' => $user->rank,
                    'user' => [
                        'id' => $user->user_id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'is_verified' => (bool) ($user->is_verified ?? false),
                    ],
                    'score' => (int) $user->points
                ];
            })->values(),
           'pagination' => [
                'total' => $total,
                'count' => $total,
                'total_pages' => ceil($total / $perPage),
                'current_page' => $page,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
            ]
        ]);
    }
    

    /**
     * @OA\Get(
     *     path="/api/v1/leaderboard/all-time",
     *     summary="Get all-time leaderboard",
     *     tags={"Leaderboard"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top users to return",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All-time leaderboard retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="position", type="integer", example=1),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="user_name", type="string", example="johndoe"),
     *                         @OA\Property(property="avatar", type="string", example="path/to/avatar.jpg"),
     *                         @OA\Property(property="is_verified", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(property="score", type="integer", example=1500)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function allTime(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
    
        // Fetch full leaderboard data
        $allLeaderboard = $this->leaderboardService->getLeaderboard('all_time');
    
        $total = $allLeaderboard->count();
        $lastPage = max(1, ceil($total / $perPage));
        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;
    
        // Slice manually for pagination
        $paginatedLeaderboard = $allLeaderboard->slice($offset, $perPage);
    
        return response()->json([
            'status' => 'success',
            'data' => $paginatedLeaderboard->map(function ($user) {
                return [
                    'position' => $user->rank,
                    'user' => [
                        'id' => $user->user_id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'is_verified' => (bool) ($user->is_verified ?? false),
                    ],
                    'score' => (int) $user->points
                ];
            })->values(),
            'pagination' => [
                'total' => $total,
                'count' => $total,
                'total_pages' => ceil($total / $perPage),
                'current_page' => $page,
                'from' => $from,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'to' => $to,
            ]
        ]);
    }
    
    
}
