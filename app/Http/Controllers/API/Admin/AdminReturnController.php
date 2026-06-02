<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $returns = ReturnRequest::with(['user', 'order'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return response()->json($returns);
    }

    public function update(Request $request, ReturnRequest $returnRequest): JsonResponse
    {
        $request->validate([
            'status'      => 'required|in:approved,rejected,completed',
            'admin_notes' => 'nullable|string',
        ]);

        $returnRequest->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json($returnRequest->fresh(['user', 'order']));
    }
}
