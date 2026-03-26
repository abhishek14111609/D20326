<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\AgoraService;

class AgoraTokenController extends Controller
{
    public function createToken(Request $request, AgoraService $agora)
    {
        $request->validate([
            'channel' => 'required|string'
        ]);

        $token = $agora->generateToken($request->channel);

        return response()->json([
            'channel' => $request->channel,
            'token' => $token
        ]);
    }


}
