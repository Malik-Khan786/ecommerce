<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    public function index(string $orderUuid): JsonResponse
    {
        $order = Order::where('uuid', $orderUuid)
            ->with([
                'tracking' => fn($q) => $q->orderBy('created_at', 'asc'),
                'items',
            ])
            ->firstOrFail();

        return response()->json($order);
    }
}
