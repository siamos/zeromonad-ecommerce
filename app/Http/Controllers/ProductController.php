<?php

namespace App\Http\Controllers;

use App\Enums\Theme;
use App\Models\ActivitySlot;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function home(): Response
    {
        $featured = Product::with(['category', 'activityDetail'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::withCount('products')->get();

        $theme = app(GeneralSettings::class)->theme();
        $bundles = Bundle::with('items.product')
            ->active()
            ->forTheme($theme->value)
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Home', [
            'featuredActivities' => $featured,
            'featuredProducts' => $featured,
            'categories' => $categories,
            'bundles' => $bundles,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'activityDetail'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published');

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.el')) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->date) {
            $query->whereHas('activityDetail', fn ($q) => $q->whereDate('event_date', $request->date));
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'date_asc' => $query->join('activity_details', 'products.id', '=', 'activity_details.product_id')
                ->orderBy('activity_details.event_date'),
            'name_asc' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))"),
            default => $query->orderByDesc('featured')->latest(),
        };

        $theme = app(GeneralSettings::class)->theme();
        $bundles = Bundle::with('items.product')
            ->active()
            ->forTheme($theme->value)
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Shop', [
            'activities' => $query->paginate(12)->withQueryString(),
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::withCount('products')->get(),
            'filters' => $request->only(['category', 'search', 'max_price', 'date', 'sort']),
            'bundles' => $bundles,
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'activityDetail', 'tags', 'reviews.user', 'activitySlots']);
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        $recommended = Product::with(['category', 'activityDetail', 'media'])
            ->recommended($product)
            ->get();

        $spotsRemaining = $product->activityDetail?->capacity
            ? $product->activityDetail->spotsRemaining()
            : null;

        $theme = app(GeneralSettings::class)->theme();

        $availableSlots = [];
        if ($theme === Theme::Activities) {
            $availableSlots = ActivitySlot::where('product_id', $product->id)
                ->where('date', '>=', now()->toDateString())
                ->whereColumn('booked_count', '<', 'capacity')
                ->orderBy('date')
                ->get(['id', 'date', 'capacity', 'booked_count'])
                ->map(fn ($slot) => [
                    'id' => $slot->id,
                    'date' => $slot->date->toDateString(),
                    'capacity' => $slot->capacity,
                    'spots_remaining' => $slot->spotsRemaining(),
                ])
                ->values()
                ->all();
        }

        $blockedDates = [];
        if ($theme === Theme::Bookings) {
            $blockedDates = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled']))
                ->whereNotNull('options->check_in')
                ->whereNotNull('options->check_out')
                ->get(['options'])
                ->map(fn ($item) => [
                    'check_in' => $item->options['check_in'],
                    'check_out' => $item->options['check_out'],
                ])
                ->values()
                ->all();
        }

        $isAvailable = true;
        if ($theme === Theme::Cars) {
            $today = now()->toDateString();
            $isAvailable = ! OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled']))
                ->whereNotNull('options->pickup_date')
                ->whereNotNull('options->return_date')
                ->where('options->return_date', '>=', $today)
                ->exists();
        }

        $settings = app(GeneralSettings::class);
        $productName = is_array($product->name) ? ($product->name['en'] ?? '') : (string) $product->name;
        $currency = $settings->currency ?? 'EUR';
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => match ($theme) {
                Theme::Bookings => 'LodgingBusiness',
                Theme::Cars => 'Product',
                Theme::Activities => $product->activityDetail?->event_date ? 'Event' : 'Product',
                default => 'Product',
            },
            'name' => $productName,
            'description' => $product->short_description ?? strip_tags((string) $product->description),
            'image' => $product->image_url,
            'url' => route('product.show', $product),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $product->price,
                'priceCurrency' => $currency,
                'availability' => $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => route('product.show', $product),
            ],
            'aggregateRating' => $product->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $product->reviews_avg_rating, 1),
                'reviewCount' => $product->reviews_count,
            ] : null,
        ]);

        return Inertia::render('Product', [
            'activity' => $product,
            'product' => $product,
            'recommended' => $recommended,
            'spotsRemaining' => $spotsRemaining,
            'availableSlots' => $availableSlots,
            'blockedDates' => $blockedDates,
            'isAvailable' => $isAvailable,
            'schema' => $schema,
        ]);
    }
}
