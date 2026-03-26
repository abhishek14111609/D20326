<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChallengeRequest;
use App\Http\Requests\Api\UpdateChallengeRequest;
use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the challenges.
     * 
     * @OA\Get(
     *     path="/api/challenges",
     *     summary="Get paginated list of challenges",
     *     tags={"Challenges"},
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
     *         description="Challenges retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ChallengeResource")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="last_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="to", type="integer", example=5),
     *                     @OA\Property(property="total", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $perPage = (int) request()->input('per_page', 10);
        
        $challenges = Challenge::latest()->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            // 'data' => [
                'data' => ChallengeResource::collection($challenges),
                'pagination' => [
                'total' => $challenges->total(),
                'count' => $challenges->count(),
                'total_pages' => ceil($challenges->total() / $perPage),
                'current_page' => $challenges->currentPage(),
                'from' => $challenges->firstItem() ?? 0,
                'last_page' => $challenges->lastPage(),
                'per_page' => $challenges->perPage(),
                'to' => $challenges->lastItem() ?? 0,
            ]
            // ]
        ]);
    }

    /**
     * Store a newly created challenge in storage.
     */
    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $challenge = Challenge::create($request->validated());
        
        return response()->json([
            'message' => 'Challenge created successfully',
            'data' => new ChallengeResource($challenge)
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified challenge.
     */
    public function show(Challenge $challenge): ChallengeResource
    {
        $challenge->loadCount('participants');
        return new ChallengeResource($challenge);
    }

    /**
     * Update the specified challenge in storage.
     */
    public function update(UpdateChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $challenge->update($request->validated());
        
        return response()->json([
            'message' => 'Challenge updated successfully',
            'data' => new ChallengeResource($challenge)
        ]);
    }

    /**
     * Remove the specified challenge from storage.
     */
    public function destroy(Challenge $challenge): JsonResponse
    {
        $challenge->delete();
        
        return response()->json([
            'message' => 'Challenge deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Get active challenges
     * 
     * @OA\Get(
     *     path="/api/challenges/active",
     *     summary="Get paginated list of active challenges",
     *     tags={"Challenges"},
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
     *         description="Active challenges retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ChallengeResource")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="last_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="to", type="integer", example=5),
     *                     @OA\Property(property="total", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function active(): JsonResponse
    {
        $perPage = (int) request()->input('per_page', 10);
        
        $challenges = Challenge::active()
            ->withCount('participants')
            ->latest()
            ->paginate($perPage);
            
        return response()->json([
            'status' => 'success',
           
            'data' => ChallengeResource::collection($challenges),
            'pagination' => [
                'total' => $challenges->total(),
                'count' => $challenges->count(),
                'total_pages' => ceil($challenges->total() / $perPage),
                'current_page' => $challenges->currentPage(),
                'from' => $challenges->firstItem() ?? 0,
                'last_page' => $challenges->lastPage(),
                'per_page' => $challenges->perPage(),
                'to' => $challenges->lastItem() ?? 0,
            ]
           
        ]);
    }

    /**
     * Get featured challenges
     * 
     * @OA\Get(
     *     path="/api/challenges/featured",
     *     summary="Get paginated list of featured challenges",
     *     tags={"Challenges"},
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
     *         description="Featured challenges retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ChallengeResource")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="last_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="to", type="integer", example=5),
     *                     @OA\Property(property="total", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function featured(): JsonResponse
    {
        $perPage = (int) request()->input('per_page', 10);
        
        $challenges = Challenge::featured()
            ->withCount('participants')
            ->latest()
            ->paginate($perPage);
            
        return response()->json([
            'status' => 'success',
            
            'data' => ChallengeResource::collection($challenges),
            'pagination' => [
                'total' => $challenges->total(),
                'count' => $challenges->count(),
                'total_pages' => ceil($challenges->total() / $perPage),
                'current_page' => $challenges->currentPage(),
                'from' => $challenges->firstItem() ?? 0,
                'last_page' => $challenges->lastPage(),
                'per_page' => $challenges->perPage(),
                'to' => $challenges->lastItem() ?? 0,
            ]
           
        ]);
    }
}
