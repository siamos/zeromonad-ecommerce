<?php

namespace App\Jobs;

use App\Mail\WaitlistItemAvailable;
use App\Models\Product;
use App\Models\Waitlist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyWaitlist implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    public function handle(): void
    {
        Waitlist::where('waitlistable_type', 'product')
            ->where('waitlistable_id', $this->product->id)
            ->whereNull('notified_at')
            ->each(function (Waitlist $entry) {
                Mail::to($entry->email)->send(new WaitlistItemAvailable($this->product));
                $entry->update(['notified_at' => now()]);
            });
    }
}
