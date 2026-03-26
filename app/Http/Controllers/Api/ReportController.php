<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="Report related operations"
 * )
 */
class ReportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/report/user/{user}",
     *     summary="Report a user",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to report",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Inappropriate behavior"),
     *             @OA\Property(property="evidence", type="array", @OA\Items(type="string"), example={"https://example.com/evidence1.jpg"}),
     *             @OA\Property(property="additional_info", type="object", example={"message": "User sent inappropriate messages"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User reported successfully"
     *     )
     * )
     */
    public function reportUser(Request $request, $userId)
    {
        $validator = Validator::make(array_merge($request->all(), ['user_id' => $userId]), [
            'user_id' => 'required|exists:users,id|not_in:' . Auth::id(),
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'url',
            'additional_info' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already reported
        $existingReport = Report::where('reporter_id', Auth::id())
            ->where('reported_user_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($existingReport) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already reported this user'
            ], 400);
        }

        $report = Report::create([
            'reporter_id' => Auth::id(),
            'reported_user_id' => $userId,
            'type' => 'user',
            'reason' => $request->reason,
            'status' => 'pending',
            'reported_type' => User::class,
            'reported_id' => $userId,
            'evidence' => $request->evidence,
            'additional_info' => $request->additional_info
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User reported successfully',
            'data' => $report
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/report/block/{user}",
     *     summary="Block a user",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to block",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User blocked successfully"
     *     )
     * )
     */
    public function blockUser($userId)
    {
        if ($userId == Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot block yourself'
            ], 400);
        }

        $userToBlock = User::findOrFail($userId);
        $user = Auth::user();

        // Check if already blocked
        if ($user->blockedUsers()->where('blocked_user_id', $userId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already blocked'
            ], 400);
        }

        // Add to blocked users
        $user->blockedUsers()->attach($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User blocked successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/report/blocked-users",
     *     summary="Get list of blocked users",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of blocked users"
     *     )
     * )
     */
    public function blockedUsers()
    {
        $blockedUsers = Auth::user()->blockedUsers()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->user_id, // because we aliased users.id as user_id in the relationship
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'blocked_at' => optional($user->pivot->created_at)->format('Y-m-d H:i:s')
                ];
            });
    
        return response()->json([
            'status' => 'success',
            'data' => $blockedUsers
        ]);
    }
    

    /**
     * @OA\Post(
     *     path="/api/report/unblock/{user}",
     *     summary="Unblock a user",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to unblock",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User unblocked successfully"
     *     )
     * )
     */
    public function unblockUser($userId)
    {
        $user = Auth::user();
        
        if (!$user->blockedUsers()->where('blocked_user_id', $userId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not blocked'
            ], 400);
        }

        $user->blockedUsers()->detach($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User unblocked successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/report/reasons",
     *     summary="Get list of report reasons",
     *     tags={"Reports"},
     *     @OA\Response(
     *         response=200,
     *         description="List of report reasons"
     *     )
     * )
     */
    public function reportReasons()
    {
        $reasons = [
            ['id' => 1, 'reason' => 'Inappropriate Content', 'description' => 'User is posting inappropriate content'],
            ['id' => 2, 'reason' => 'Spam', 'description' => 'User is spamming'],
            ['id' => 3, 'reason' => 'Harassment', 'description' => 'User is harassing others'],
            ['id' => 4, 'reason' => 'Impersonation', 'description' => 'User is impersonating someone else'],
            ['id' => 5, 'reason' => 'Other', 'description' => 'Other reason (please specify in description)'],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $reasons
        ]);
    }
}
