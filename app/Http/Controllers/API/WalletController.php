<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        $recent = WalletTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'wallet_balance'       => $user->wallet_balance,
            'recent_transactions'  => $recent,
        ]);
    }

    public function history(Request $request)
    {
        $history = WalletTransaction::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($history);
    }
}
