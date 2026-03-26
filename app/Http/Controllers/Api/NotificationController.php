<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get user's notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="unread_only",
     *         in="query",
     *         required=false,
     *         description="Show only unread notifications",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of notifications per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Notification")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="unread_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);
            $unreadOnly = $request->boolean('unread_only', false);
            
            // Debug: Log the authenticated user ID
            Log::info('Fetching notifications for user ID: ' . $user->id);
            
            // Get notifications with eager loading of relationships
            $query = $user->notifications()
                ->with(['fromUser'])
                ->latest();

            if ($unreadOnly) {
                $query->whereNull('read_at');
            }

            // Debug: Get the raw SQL query
            $rawSql = Str::replaceArray('?', $query->getBindings(), $query->toSql());
            Log::info('Notification Query: ' . $rawSql);
            
            $notifications = $query->paginate($perPage);
            
            // Debug: Log the number of notifications found
            Log::info('Total notifications found: ' . $notifications->total());

            return response()->json([
                'status' => 'success',
                'data' => NotificationResource::collection($notifications),
                'pagination' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'unread_count' => $user->unreadNotifications()->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch notifications',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     summary="Get unread notifications count",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="unread_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());
        
        return $this->successResponse([
            'unread_count' => $count,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/mark-as-read",
     *     summary="Mark notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notification_ids"},
     *             @OA\Property(property="notification_ids", type="array", @OA\Items(type="integer"), example={1,2,3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notifications marked as read"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="updated_count", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user();
        
        // Debug: Log the user ID and notification IDs being processed
        Log::info('Marking notifications as read', [
            'user_id' => $user->id,
            'notification_ids' => $request->input('notification_ids')
        ]);

        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => [
                'integer',
                function ($attribute, $value, $fail) use ($user) {
                    $exists = Notification::where('id', $value)
                        ->where('user_id', $user->id)
                        ->exists();
                        
                    // Debug: Log the check for each notification ID
                    Log::debug('Checking notification ownership', [
                        'notification_id' => $value,
                        'user_id' => $user->id,
                        'exists' => $exists
                    ]);
                    
                    if (!$exists) {
                        $fail("The selected notification ID is invalid or doesn't belong to you.");
                    }
                }
            ]
        ]);

        $count = $this->notificationService->markAsRead(
            $request->input('notification_ids'),
            $user
        );

        return $this->successResponse(
            ['updated_count' => $count],
            'Notifications marked as read'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/mark-all-read",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read")
     *         )
     *     )
     * )
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        // Debug: Log the user ID and number of unread notifications
        $unreadCount = $user->unreadNotifications()->count();
        Log::info('Marking all notifications as read', [
            'user_id' => $user->id,
            'unread_count' => $unreadCount
        ]);

        $count = $this->notificationService->markAllAsRead($user);
        
        // Debug: Log the result of the update
        Log::debug('Marked notifications as read', [
            'user_id' => $user->id,
            'updated_count' => $count
        ]);

        if ($count === 0) {
            return response()->json([
                'status' => 'info',
                'message' => 'No unread notifications to mark as read'
            ], 200);
        }

        return $this->successResponse(
            ['updated_count' => $count],
            $count === 1 ? '1 notification marked as read' : "{$count} notifications marked as read"
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{notification}",
     *     summary="Delete a notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="notification",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notification deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $notification->delete();

        return $this->successResponse(
            null,
            'Notification deleted'
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications",
     *     summary="Delete all notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="read_only",
     *         in="query",
     *         required=false,
     *         description="Delete only read notifications",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notifications deleted"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deleted_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function clearAll(Request $request)
    {
        $readOnly = $request->boolean('read_only', false);
        
        $deletedCount = $this->notificationService->deleteAll(
            $request->user(),
            $readOnly
        );

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            'Notifications cleared'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/test",
     *     summary="Create a test notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Test notification created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Test notification created"),
     *             @OA\Property(property="notification", ref="#/components/schemas/Notification")
     *         )
     *     )
     * )
     */
    public function testNotification()
    {
        try {
            $user = auth()->user();
            
            $notification = $this->notificationService->create(
                $user,
                Notification::TYPE_SYSTEM,
                'This is a test notification',
                null,
                ['test' => true]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Test notification created',
                'notification' => new NotificationResource($notification->load('fromUser'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create test notification',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
