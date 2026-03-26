<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Stripe;
use App\Models\GiftTransaction;
use App\Services\GiftService;
use DB;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            if ($intent->metadata->type === 'gift') {
                if (GiftTransaction::where('payment_intent_id', $intent->id)->exists()) {
                    return response()->json(['ok' => true]);
                }

                DB::transaction(function () use ($intent) {
                    app(GiftService::class)->sendGift(
                        $intent->metadata->sender_id,
                        $intent->metadata->receiver_id,
                        $intent->metadata->gift_id
                    );

                    GiftTransaction::create([
                        'payment_intent_id' => $intent->id,
                        'sender_id' => $intent->metadata->sender_id,
                        'receiver_id' => $intent->metadata->receiver_id,
                        'gift_id' => $intent->metadata->gift_id,
                        'amount' => $intent->amount / 100,
                        'receiver_amount' => ($intent->amount / 100) * 0.70,
                        'platform_amount' => ($intent->amount / 100) * 0.30,
                        'status' => 'paid',
                    ]);
                });
            }
        }

        return response()->json(['status' => 'success']);
    }
}
