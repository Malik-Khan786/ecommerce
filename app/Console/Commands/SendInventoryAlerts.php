<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInventoryAlerts extends Command
{
    protected $signature   = 'inventory:send-alerts';
    protected $description = 'Send inventory alert emails to admin for low/out-of-stock products';

    public function handle(): void
    {
        $lowStock   = Product::where('stock', '>', 0)->where('stock', '<=', 10)->get();
        $outOfStock = Product::where('stock', 0)->get();

        if ($lowStock->isEmpty() && $outOfStock->isEmpty()) {
            $this->info('No inventory issues found.');
            return;
        }

        $adminEmail = config('mail.admin_address', 'admin@huzaifamobile.pk');

        Mail::send('emails.inventory-alert', [
            'lowStock'   => $lowStock,
            'outOfStock' => $outOfStock,
        ], function ($message) use ($adminEmail) {
            $message->to($adminEmail)
                    ->subject('Inventory Alert — ' . now()->format('M j, Y'));
        });

        $this->info("Inventory alert sent to {$adminEmail}. Low stock: {$lowStock->count()}, Out of stock: {$outOfStock->count()}");
    }
}
