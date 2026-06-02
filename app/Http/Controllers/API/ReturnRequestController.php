<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'reason'      => 'required|in:damaged,wrong_item,not_received,changed_mind,other',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'Return requests can only be submitted for delivered orders.'], 422);
        }

        $existing = ReturnRequest::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'A return request for this order already exists.'], 422);
        }

        $returnRequest = ReturnRequest::create([
            'order_id'    => $order->id,
            'user_id'     => $request->user()->id,
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        return response()->json($returnRequest, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $returnRequests = ReturnRequest::with('order')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($returnRequests);
    }

    public function show(Request $request, ReturnRequest $returnRequest): JsonResponse
    {
        if ($returnRequest->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json($returnRequest->load('order'));
    }
}
