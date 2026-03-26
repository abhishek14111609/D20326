<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Payment Intent
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount'   => 'required|integer|min:1',
            'currency' => 'sometimes|string|size:3',
            'metadata' => 'sometimes|array',
        ]);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount'   => $request->amount,
                'currency' => $request->currency ?? 'usd',
                'metadata' => array_merge($request->metadata ?? [], [
                    'user_id' => $request->user()->id,
                ]),
            ]);

            Payment::create([
                'user_id'           => $request->user()->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount'            => $paymentIntent->amount,
                'currency'          => $paymentIntent->currency,
                'status'            => $paymentIntent->status,
                'metadata'          => $paymentIntent->metadata,
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Stripe Error: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment intent creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stripe Webhook Handler
     */
    public function handleWebhook(Request $request)
    {
        $payload       = $request->getContent();
        $sig_header    = $request->header('Stripe-Signature');
        $secret        = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $secret
            );
        } catch (\Exception $e) {
            return response('Invalid Signature', 400);
        }

        $paymentIntent = $event->data->object;

        switch ($event->type) {

            case 'payment_intent.succeeded':
                $this->paymentSucceeded($paymentIntent);
                break;

            case 'payment_intent.payment_failed':
                $this->paymentFailed($paymentIntent);
                break;

            default:
                break;
        }

        return response('OK', 200);
    }

    /**
     * PAYMENT SUCCESS
     */
   protected function paymentSucceeded($pi)
    {
        $payment = Payment::where('payment_intent_id', $pi->id)->first();

        if ($payment) {
            $payment->update([
                'status'        => 'succeeded',
                'payment_method'=> $pi->payment_method_types[0] ?? null,
                'receipt_url'   => $pi->charges->data[0]->receipt_url ?? null,
            ]);
        }
    } 




    /**
     * PAYMENT FAILED
     */
    protected function paymentFailed($pi)
    {
        Payment::where('payment_intent_id', $pi->id)
               ->update(['status' => 'failed']);
    }

    /**
     * CHECK PAYMENT STATUS
     */
    public function getPaymentStatus($paymentIntentId)
    {
        try {
            $pi = PaymentIntent::retrieve($paymentIntentId);

            return response()->json([
                'success' => true,
                'status'  => $pi->status,
                'payment_intent' => $pi,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
