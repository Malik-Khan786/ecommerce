<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'order_uuid' => 'required|string',
        ]);

        $order = Order::where('uuid', $request->order_uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount'   => (int) round($order->total * 100), // amount in paisa (smallest unit)
            'currency' => 'pkr',
            'metadata' => [
                'order_uuid'   => $order->uuid,
                'order_number' => $order->order_number,
            ],
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent    = $event->data->object;
            $orderUuid = $intent->metadata->order_uuid ?? null;

            if ($orderUuid) {
                Order::where('uuid', $orderUuid)->update([
                    'payment_status' => 'paid',
                ]);
            }
        }

        return response()->json(['received' => true]);
    }
}
