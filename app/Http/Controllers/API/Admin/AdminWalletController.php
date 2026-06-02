<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class AdminWalletController extends Controller
{
    public function credit(Request $request, User $user)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $user->increment('wallet_balance', $request->amount);

        WalletTransaction::create([
            'user_id'     => $user->id,
            'amount'      => $request->amount,
            'type'        => 'credit',
            'description' => $request->description,
        ]);

        return response()->json([
            'message'         => "Rs. {$request->amount} credited to {$user->name}'s wallet.",
            'wallet_balance'  => $user->fresh()->wallet_balance,
        ]);
    }
}
