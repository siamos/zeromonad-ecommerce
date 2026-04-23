<?php

namespace App\Jobs;

use App\Mail\StockAlertNotification;
use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyStockAlert implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    public function handle(): void
    {
        StockAlert::where('product_id', $this->product->id)
            ->whereNull('notified_at')
            ->each(function (StockAlert $alert) {
                Mail::send(new StockAlertNotification($this->product, $alert->email));
                $alert->update(['notified_at' => now()]);
            });
    }
}
