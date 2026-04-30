<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySlot;
use App\Models\Category;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function home(): Response
    {
        $featured = Activity::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::forTheme('Activities')->get();

        return Inertia::render('Home', [
            'featuredActivities' => $featured,
            'categories' => $categories,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Activity::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published');

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.el')) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->location) {
            $query->where('location', 'like', '%'.$request->location.'%');
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->max_duration) {
            $query->where('duration_minutes', '<=', $request->integer('max_duration'));
        }

        if ($request->date) {
            $query->whereHas('slots', fn ($q) => $q->whereDate('date', $request->date)
                ->whereColumn('booked_count', '<', 'capacity'));
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->min_age) {
            $query->where('min_age', '<=', $request->integer('min_age'));
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))"),
            default => $query->orderByDesc('featured')->latest(),
        };

        return Inertia::render('Shop', [
            'activities' => $query->paginate(12)->withQueryString(),
            'categories' => Category::forTheme('Activities')->get(),
            'filters' => $request->only(['category', 'search', 'location', 'min_price', 'max_price', 'max_duration', 'date', 'sort', 'difficulty', 'min_age']),
        ]);
    }

    public function show(Activity $activity): Response
    {
        $activity->load(['category', 'reviews.user']);
        $activity->loadAvg('reviews', 'rating');
        $activity->loadCount('reviews');

        $recommended = Activity::with(['category', 'media'])
            ->recommended($activity)
            ->get();

        $availableSlots = ActivitySlot::where('activity_id', $activity->id)
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

        $settings = app(GeneralSettings::class);
        $title = is_array($activity->title) ? ($activity->title['en'] ?? '') : (string) $activity->title;

        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $title,
            'description' => is_array($activity->short_description) ? ($activity->short_description['en'] ?? '') : (string) ($activity->short_description ?? ''),
            'image' => $activity->image_url,
            'url' => route('product.show', $activity),
            'location' => $activity->location ? [
                '@type' => 'Place',
                'name' => $activity->location,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $activity->price,
                'priceCurrency' => $settings->currency ?? 'EUR',
            ],
            'aggregateRating' => $activity->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $activity->reviews_avg_rating, 1),
                'reviewCount' => $activity->reviews_count,
            ] : null,
        ]);

        return Inertia::render('Product', [
            'activity' => $activity,
            'recommended' => $recommended,
            'availableSlots' => $availableSlots,
            'schema' => $schema,
        ]);
    }
}
