<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgoraTokenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AgoraController extends Controller
{
    protected $agora;

    public function __construct(AgoraTokenService $agora)
    {
        $this->agora = $agora;
    }

    public function generateToken(Request $request)
{
    $request->validate([
        'channel' => 'required',
        'uid' => 'required'
    ]);

    $token = (new AgoraTokenService)->generate(
        $request->channel,
        $request->uid
    );

    return response()->json([
        'token' => $token
    ]);
}


public function validateToken(Request $request)
{
    $request->validate([
        'token' => 'required'
    ]);

    $service = new AgoraTokenService();
    return response()->json($service->validateToken($request->token));
}


}
