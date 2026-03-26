<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    private $fcm;

    public function __construct(FirebaseNotificationService $fcm)
    {
        $this->fcm = $fcm;
    }

    public function send(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'title' => 'required',
            'body'  => 'required',
        ]);

        $result = $this->fcm->sendNotification(
            $request->token,
            $request->title,
            $request->body,
            $request->data ?? []
        );

        return response()->json($result);
    }
}
