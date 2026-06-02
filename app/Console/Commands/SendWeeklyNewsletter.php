<?php

namespace App\Console\Commands;

use App\Mail\WeeklyNewsletter;
use App\Models\EmailLead;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyNewsletter extends Command
{
    protected $signature   = 'newsletter:send-weekly';
    protected $description = 'Send weekly newsletter to all email leads';

    public function handle(): int
    {
        $leads = EmailLead::where(function ($q) {
            $q->whereNull('notified_at')
              ->orWhere('notified_at', '<=', now()->subDays(7));
        })->get();

        if ($leads->isEmpty()) {
            $this->info('No leads to notify.');
            return self::SUCCESS;
        }

        $products = Product::where('is_featured', true)
            ->where('is_active', true)
            ->latest()
            ->take(4)
            ->get();

        $sent = 0;
        foreach ($leads as $lead) {
            try {
                Mail::to($lead->email)->queue(new WeeklyNewsletter($products));
                $lead->update(['notified_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $this->warn("Failed to send to {$lead->email}: " . $e->getMessage());
            }
        }

        $this->info("Newsletter sent to {$sent} leads.");
        return self::SUCCESS;
    }
}
