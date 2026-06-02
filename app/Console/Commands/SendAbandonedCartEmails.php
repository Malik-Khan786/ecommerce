<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartRecovery;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails extends Command
{
    protected $signature   = 'carts:send-recovery-emails';
    protected $description = 'Send recovery emails to users with abandoned carts';

    public function handle(): void
    {
        $cutoff = now()->subHours(2);

        // Find cart items older than 2 hours with a logged-in user, not yet sent recovery
        $userIds = Cart::whereNotNull('user_id')
            ->whereNull('recovery_sent_at')
            ->where('created_at', '<=', $cutoff)
            ->distinct()
            ->pluck('user_id');

        $sent  = 0;
        $skipped = 0;

        foreach ($userIds as $userId) {
            // Skip if user has placed an order in the last 2 hours
            $hasRecentOrder = Order::where('user_id', $userId)
                ->where('created_at', '>=', $cutoff)
                ->exists();

            if ($hasRecentOrder) {
                $skipped++;
                continue;
            }

            $cartItems = Cart::with('product')
                ->where('user_id', $userId)
                ->whereNull('recovery_sent_at')
                ->where('created_at', '<=', $cutoff)
                ->get();

            if ($cartItems->isEmpty()) {
                continue;
            }

            $user = $cartItems->first()->user;

            if (!$user || !$user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new AbandonedCartRecovery($user, $cartItems));

                // Mark all these cart items as sent
                Cart::where('user_id', $userId)
                    ->whereNull('recovery_sent_at')
                    ->where('created_at', '<=', $cutoff)
                    ->update(['recovery_sent_at' => now()]);

                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send to user {$userId}: " . $e->getMessage());
            }
        }

        $this->info("Abandoned cart emails sent: {$sent}, skipped (recent order): {$skipped}");
    }
}
