<?php

use App\Actions\Blog\GenerateBlogPost;
use App\Jobs\SendAbandonedCartReminder;
use App\Models\Cart;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the blog generation as an Artisan command via Action
Artisan::command('blog:generate {--topic= : Custom topic for the blog post}', function () {
    GenerateBlogPost::make()->asCommand($this);
})->purpose('Generate an AI blog post');

// Daily AI blog post at 08:00
Schedule::call(fn () => GenerateBlogPost::run())->dailyAt('08:00');

// Abandoned cart recovery: hourly, target carts idle >2h with no completed order
Schedule::call(function () {
    Cart::with(['user', 'items'])
        ->whereHas('user')
        ->whereHas('items')
        ->where('updated_at', '<=', now()->subHours(2))
        ->whereDoesntHave('user.orders', fn ($q) => $q->where('created_at', '>=', now()->subHours(4)))
        ->whereNull('reminder_sent_at')
        ->each(function (Cart $cart) {
            dispatch(new SendAbandonedCartReminder($cart));
            $cart->updateQuietly(['reminder_sent_at' => now()]);
        });
})->hourly()->name('abandoned-cart-reminder');
