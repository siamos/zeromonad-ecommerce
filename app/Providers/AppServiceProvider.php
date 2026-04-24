<?php

namespace App\Providers;

use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vehicle;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\PaymentGateways\PaymentGatewayManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager($app);
        });
    }

    public function boot(): void
    {
        Relation::morphMap([
            'product' => Product::class,
            'activity' => Activity::class,
            'accommodation' => Accommodation::class,
            'vehicle' => Vehicle::class,
        ]);

        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);

        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());
    }
}
