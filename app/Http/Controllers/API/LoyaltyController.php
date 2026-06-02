<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'points'       => $user->loyalty_points,
            'value_in_rs'  => $user->loyalty_points,
            'can_redeem'   => $user->loyalty_points >= 100,
            'min_redeem'   => 100,
        ]);
    }

    public function history(Request $request)
    {
        $history = LoyaltyPoint::where('user_id', $request->user()->id)
            ->with('order:id,uuid,order_number')
            ->orderByDesc('created_at')
            ->paginate(15);
        return response()->json($history);
    }

    public function redeem(Request $request)
    {
        $request->validate(['points' => 'required|integer|min:100']);

        $user = $request->user();
        if ($user->loyalty_points < $request->points) {
            return response()->json(['message' => 'Insufficient loyalty points.'], 422);
        }

        $user->decrement('loyalty_points', $request->points);

        LoyaltyPoint::create([
            'user_id'     => $user->id,
            'points'      => -$request->points,
            'type'        => 'redeemed',
            'description' => "Redeemed {$request->points} points for Rs. {$request->points} discount",
        ]);

        return response()->json([
            'message'          => 'Points redeemed successfully!',
            'discount_amount'  => $request->points,
            'remaining_points' => $user->loyalty_points,
        ]);
    }
}
