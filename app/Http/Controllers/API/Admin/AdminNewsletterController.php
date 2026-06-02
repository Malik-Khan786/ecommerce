<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WeeklyNewsletter;
use App\Models\EmailLead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class AdminNewsletterController extends Controller
{
    public function send(): JsonResponse
    {
        $products = Product::where('is_featured', true)
            ->where('is_active', true)
            ->latest()
            ->take(4)
            ->get();

        $leads = EmailLead::all();

        $queued = 0;
        foreach ($leads as $lead) {
            try {
                Mail::to($lead->email)->queue(new WeeklyNewsletter($products));
                $lead->update(['notified_at' => now()]);
                $queued++;
            } catch (\Exception $e) {
                \Log::warning("Newsletter failed for {$lead->email}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => "Newsletter queued for {$queued} lead(s).",
            'count'   => $queued,
        ]);
    }
}
