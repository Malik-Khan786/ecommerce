<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAckController extends Controller
{
    // GET /api/ack/{token} — return order info for the acknowledgement page
    public function show(string $token)
    {
        $order = Order::where('ack_token', $token)
            ->with('items')
            ->firstOrFail();

        return response()->json([
            'order_number' => $order->order_number,
            'shipping_name'=> $order->shipping_name,
            'total'        => $order->total,
            'status'       => $order->status,
            'ack_status'   => $order->ack_status,
            'items'        => $order->items->map(fn($i) => [
                'name'     => $i->product_name,
                'quantity' => $i->quantity,
                'subtotal' => $i->subtotal,
            ]),
        ]);
    }

    // POST /api/ack/{token} — customer submits acknowledgement
    public function confirm(Request $request, string $token)
    {
        $order = Order::where('ack_token', $token)->firstOrFail();

        if ($order->ack_status !== null) {
            return response()->json(['message' => 'Already acknowledged.'], 422);
        }

        $request->validate([
            'ack_status' => 'required|in:received,issue',
            'ack_message'=> 'nullable|string|max:500',
        ]);

        $order->update([
            'ack_status'  => $request->ack_status,
            'ack_message' => $request->ack_message,
            'ack_at'      => now(),
        ]);

        return response()->json(['message' => 'Thank you for your feedback!']);
    }
}
