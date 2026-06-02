<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class PublishScheduledProducts extends Command
{
    protected $signature   = 'products:publish-scheduled';
    protected $description = 'Publish products whose scheduled_at time has passed';

    public function handle(): void
    {
        $count = Product::where('is_active', false)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->update(['is_active' => true]);

        $this->info("Published {$count} scheduled product(s).");
    }
}
