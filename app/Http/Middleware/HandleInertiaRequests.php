<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\Wishlist;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $settings = app(GeneralSettings::class);

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),
            ],
            'current_theme' => $settings->active_theme,
            'site_name' => $settings->site_name,
            'currency' => $settings->currency,
            'site_description' => $settings->site_description,
            'site_logo_url' => $settings->site_logo
                ? Storage::disk('public')->url($settings->site_logo)
                : null,
            'hero_title' => $settings->hero_title,
            'hero_subtitle' => $settings->hero_subtitle,
            'hero_image_url' => $settings->hero_image
                ? Storage::disk('public')->url($settings->hero_image)
                : null,
            'low_stock_threshold' => $settings->low_stock_threshold,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
            'cart_item_count' => fn () => Cart::query()
                ->when(
                    auth()->check(),
                    fn ($q) => $q->where('user_id', auth()->id()),
                    fn ($q) => $q->where('session_id', session()->getId())->whereNull('user_id')
                )
                ->withSum('items', 'quantity')
                ->first()
                ?->items_sum_quantity ?? 0,
            'wishlist_ids' => fn () => auth()->check()
                ? Wishlist::where('user_id', auth()->id())->pluck('product_id')->all()
                : [],
            'locale' => app()->getLocale(),
            'translations' => fn () => json_decode(
                file_get_contents(
                    file_exists(lang_path(app()->getLocale().'.json'))
                        ? lang_path(app()->getLocale().'.json')
                        : lang_path('en.json')
                ),
                true
            ) ?? [],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}
