<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function myCode(Request $request)
    {
        $user = $request->user();

        if (!$user->referral_code) {
            $code = strtoupper(substr(md5($user->id . $user->email), 0, 8));
            $user->update(['referral_code' => $code]);
        }

        $totalReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'referral_code'   => $user->referral_code,
            'total_referrals' => $totalReferrals,
            'reward_per_referral' => 200,
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user    = $request->user();
        $referrer = User::where('referral_code', strtoupper($request->code))->first();

        if (!$referrer) {
            return response()->json(['message' => 'Invalid referral code.'], 422);
        }

        if ($referrer->id === $user->id) {
            return response()->json(['message' => 'You cannot use your own referral code.'], 422);
        }

        $existing = Referral::where('referred_id', $user->id)->where('status', 'completed')->first();
        if ($existing) {
            return response()->json(['message' => 'You have already used a referral code.'], 422);
        }

        Referral::create([
            'referrer_id'   => $referrer->id,
            'referred_id'   => $user->id,
            'referral_code' => strtoupper($request->code),
            'status'        => 'completed',
            'reward_points' => 200,
        ]);

        $referrer->increment('loyalty_points', 200);
        LoyaltyPoint::create([
            'user_id'     => $referrer->id,
            'points'      => 200,
            'type'        => 'bonus',
            'description' => "Referral bonus — {$user->name} joined using your code",
        ]);

        return response()->json(['message' => 'Referral applied! Your friend earned 200 points.']);
    }
}
