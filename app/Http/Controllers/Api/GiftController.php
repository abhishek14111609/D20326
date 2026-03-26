<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GiftCollection;
use App\Http\Resources\Api\GiftResource;
use App\Services\GiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Models\User;
use App\Models\VideoCall;
use App\Models\UserGift;
use App\Models\Gift;
use App\Models\GiftTransaction;
use DB;
use App\Models\WalletTransaction;
/**
 * @OA\Tag(
 *     name="Gifts",
 *     description="Gift related operations"
 * )
 */
class GiftController extends Controller
{
    protected $giftService;

    public function __construct(GiftService $giftService)
    {
        $this->giftService = $giftService;
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/gifts",
     *     summary="Get all available gifts",
     *     tags={"Gifts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of available gifts",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Gift")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=0),
     *                 @OA\Property(property="count", type="integer", example=0),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->query('per_page', 10);
        
        $gifts = Gift::orderBy('price', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
		
		$items = $gifts->getCollection()->map(function ($gift) {
        return [
            "id"          => $gift->id,
            "name"        => $gift->name,
            "description" => $gift->description,
            'image_url' => 'https://duos.webvibeinfotech.in/storage/app/public/' . $gift->image_path ,
             "price"        => $gift->price,
            "category_id"  => $gift->category_id,
            "is_active"    => (bool) $gift->is_active,
            "sender_id"    => $gift->sender_id ?? '',
            "receiver_id"  => $gift->receiver_id ?? '',
            "type"         => $gift->type,
            "context"      => $gift->context ?? '',

            "created_at"   => $gift->created_at?->toISOString(),
            "updated_at"   => $gift->updated_at?->toISOString(),
            "deleted_at"   => $gift->deleted_at?->toISOString() ?? '',
        ];
    });
            
        return response()->json([
            'status' => 'success',
            'data' => $items,
           'pagination' => [
                'total' => $gifts->total(),
                'count' => $gifts->count(),
                'total_pages' => ceil($gifts->total() / $perPage),
                'current_page' => $gifts->currentPage(),
                'from' => $gifts->firstItem() ?? 0,
                'last_page' => $gifts->lastPage(),
                'per_page' => $gifts->perPage(),
                'to' => $gifts->lastItem() ?? 0,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/gifts/send/{user}",
     *     summary="Send a gift to a user",
     *     tags={"Gifts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to send the gift to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gift_id"},
     *             @OA\Property(property="gift_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Here's a gift for you!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Gift sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Gift sent successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserGift")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Insufficient balance or gift not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
public function sendGift(Request $request, $userId): JsonResponse
{
    try {
        $validated = $request->validate([
            'gift_id'           => 'required|exists:gifts,id',
            'amount'            => 'required|numeric|min:1',
            'payment_intent_id' => 'required|string',
            'status'            => 'required|string',
            'message'           => 'nullable|string|max:255',
        ]);

        $sender   = auth()->user();
        $receiver = User::findOrFail($userId);
        $gift     = Gift::findOrFail($validated['gift_id']);

        // 🛑 Duplicate payment protection
        if (GiftTransaction::where('payment_intent_id', $validated['payment_intent_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment already processed'
            ], 409);
        }

        $responseData = [];

        DB::transaction(function () use (
            $validated, $sender, $receiver, $gift, &$responseData
        ) {

            $amount         = $validated['amount'];
            $receiverAmount = round($amount * 0.70, 2);
            $platformAmount = round($amount * 0.30, 2);

            // 🎁 USER GIFT
            $userGift = UserGift::create([
                'sender_id'   => $sender->id,
                'receiver_id' => $receiver->id,
                'gift_id'     => $gift->id,
                'price'       => $amount,
                'message'     => $validated['message'] ?? null,
            ]);

            // 👛 WALLET
            $lastBalance = WalletTransaction::where('user_id', $receiver->id)
                ->latest('id')
                ->value('balance_after') ?? 0;

            $newBalance = $lastBalance + $receiverAmount;

            WalletTransaction::create([
                'user_id'        => $receiver->id,
                'type'           => 'gift_credit',
                'amount'         => $receiverAmount,
                'balance_after'  => $newBalance,
                'description'    => 'Gift received',
                'reference_type' => UserGift::class,
                'reference_id'   => $userGift->id,
                'metadata'       => json_encode([
                    'gift_id' => $gift->id,
                    'sender_id' => $sender->id,
                    'payment_intent_id' => $validated['payment_intent_id'],
                ]),
            ]);

            // 📄 TRANSACTION
            GiftTransaction::create([
                'payment_intent_id' => $validated['payment_intent_id'],
                'sender_id'         => $sender->id,
                'receiver_id'       => $receiver->id,
                'gift_id'           => $gift->id,
                'amount'            => $amount,
                'receiver_amount'   => $receiverAmount,
                'platform_amount'   => $platformAmount,
                'status'            => $validated['status'],
            ]);

            // 📦 RESPONSE DATA
            $responseData = [
                'gift' => [
                    'gift_id' => $gift->id,
                    'gift_name' => $gift->name,
                    'price' => $amount,
                    'message' => $userGift->message,
                ],
                'receiver_wallet' => [
                    'credited_amount' => $receiverAmount,
                    'balance_after' => $newBalance,
                ],
                'transaction' => [
                    'payment_intent_id' => $validated['payment_intent_id'],
                    'status' => $validated['status'],
                ],
            ];
        });

        // ✅ REAL RETURN (OUTSIDE TRANSACTION)
        return response()->json([
            'status'  => 'success',
            'message' => 'Gift sent successfully',
            'data'    => $responseData,
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}/gifts",
     *     summary="Get all gifts received by a user",
     *     tags={"Gifts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user who received the gifts",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of gifts received by the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserGift")
     *             )
     *         )
     *     )
     * )
     */
    public function userGifts($userId): JsonResponse
    {
        $gifts = $this->giftService->getUserReceivedGifts($userId);
        
        return response()->json([
            'status' => 'success',
            'data' => new GiftCollection($gifts)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/gifts/sent",
     *     summary="Get all gifts sent by the authenticated user",
     *     tags={"Gifts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of gifts sent by the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserGift")
     *             )
     *         )
     *     )
     * )
     */
    public function sentGifts(): JsonResponse
    {
        $gifts = $this->giftService->getUserSentGifts(auth()->id());
        
        return response()->json([
            'status' => 'success',
            'data' => new GiftCollection($gifts)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/video-call/initiate/{user}",
     *     summary="Initiate a video call with another user",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to call",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"call_type"},
     *             @OA\Property(property="call_type", type="string", enum={"audio", "video"}, example="video", description="Type of call: 'audio' or 'video'"),
     *             @OA\Property(property="duration_minutes", type="integer", example=5, description="Estimated call duration in minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video call initiated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Video call initiated"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="call_id", type="string", example="call_123456789"),
     *                 @OA\Property(property="caller_id", type="integer", example=1),
     *                 @OA\Property(property="receiver_id", type="integer", example=2),
     *                 @OA\Property(property="call_type", type="string", example="video"),
     *                 @OA\Property(property="status", type="string", example="initiated"),
     *                 @OA\Property(property="call_cost", type="number", format="float", example=10.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or insufficient balance",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function initiateVideoCall(Request $request, $userId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'call_type' => 'required|in:audio,video',
                'duration_minutes' => 'required|integer|min:1|max:60',
            ]);

            $caller = $request->user();
            $receiver = User::findOrFail($userId);
            
            // Calculate call cost (example: $0.10 per minute for audio, $0.20 for video)
            $ratePerMinute = $validated['call_type'] === 'video' ? 0.20 : 0.10;
            $callCost = round($validated['duration_minutes'] * $ratePerMinute, 2);

            // Check if user has sufficient balance
            if ($caller->balance < $callCost) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient balance. Please add funds to make a call.',
                    'required_balance' => $callCost,
                    'current_balance' => $caller->balance
                ], 400);
            }

            // Deduct call cost from balance
            $caller->decrement('balance', $callCost);

            // Create call record (you'll need to create this migration and model)
            $call = VideoCall::create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'call_type' => $validated['call_type'],
                'status' => 'initiated',
                'call_cost' => $callCost,
                'duration_minutes' => $validated['duration_minutes']
            ]);

            // Here you would typically integrate with a WebRTC service or similar
            // to establish the actual video call connection
            
            return response()->json([
                'status' => 'success',
                'message' => 'Video call initiated',
                'data' => [
                    'call_id' => 'call_' . $call->id,
                    'caller_id' => $call->caller_id,
                    'receiver_id' => $call->receiver_id,
                    'call_type' => $call->call_type,
                    'status' => $call->status,
                    'call_cost' => $call->call_cost,
                    'duration_minutes' => $call->duration_minutes
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate video call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/gifts/history",
     *     summary="Get authenticated user's gift history",
     *     tags={"Gifts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by gift type: 'sent' or 'received'",
     *         required=false,
     *         @OA\Schema(type="string", enum={"sent", "received"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gift history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/UserGift")
     *                 ),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="to", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=50)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function giftHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->input('type');
        $perPage = $request->input('per_page', 10);
        
        $query = UserGift::with(['gift', 'sender', 'receiver'])
            ->where(function($q) use ($user, $type) {
                $q->where('receiver_id', $user->id);
                
                if ($type === 'sent') {
                    $q->orWhere('sender_id', $user->id);
                } elseif ($type === 'received') {
                    $q->where('receiver_id', $user->id);
                } else {
                    // Default: show both sent and received gifts
                    $q->orWhere('sender_id', $user->id);
                }
            })
            ->orderBy('created_at', 'desc');
        
        $gifts = $query->paginate(min($perPage, 100));
        
        return response()->json([
            'status' => 'success',
            'data' => GiftResource::collection($gifts->items()),
            'pagination' => [
                'total' => $gifts->total(),
                'count' => $gifts->count(),
                'total_pages' => ceil($gifts->total() / $perPage),
                'current_page' => $gifts->currentPage(),
                'from' => $gifts->firstItem() ?? 0,
                'last_page' => $gifts->lastPage(),
                'per_page' => $gifts->perPage(),
                'to' => $gifts->lastItem() ?? 0,
            ]
        ]);
    }
	
	
public function fetchWallet(int $userId): JsonResponse
    {
        $user = User::with('profile')->find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 💰 Total Wallet Amount
        // $Wallet = WalletTransaction::where('user_id', $userId)->first();
	
		//$Wallet = WalletTransaction::whereJsonContains('metadata->sender_id', $userId)->get();
		
		$walletTransactions = WalletTransaction::all();

		// Filter the transactions based on metadata's sender_id
$Wallet = $walletTransactions
    ->filter(function ($transaction) use ($userId) {
        $decodedMeta = json_decode($transaction->metadata, true);
        return isset($decodedMeta['sender_id']) && $decodedMeta['sender_id'] == $userId;
    })
    ->values(); // 👈 IMPORTANT

	
	
        // 🎁 Sent Gifts
		$sentGifts = UserGift::where('receiver_id', $userId)->orWhere('sender_id', $userId)->get();
	
        return response()->json([
            'status' => true,
            'data' => [

                // 👤 USER DETAILS OBJECT
                'user_details' => [
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'mobile'  => optional($user->profile)->mobile,
                ],

                // 💼 WALLET OBJECT
                'wallet' => $Wallet,
					//'user_id' => $Wallet->user_id,
                    //'total_amount' => $Wallet->amount ?? '',
                

                // 🎁 GIFT TRANSACTION OBJECT
                'sent_gifts' => 
					$sentGifts
				
            ]
        ]);
    }
	
}
