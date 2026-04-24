<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Category;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccommodationController extends Controller
{
    public function home(): Response
    {
        $featured = Accommodation::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::forTheme('Bookings')->get();

        return Inertia::render('Home', [
            'featuredAccommodations' => $featured,
            'categories' => $categories,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Accommodation::with('category')
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

        if ($request->max_price) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        if ($request->min_bedrooms) {
            $query->where('bedrooms', '>=', $request->integer('min_bedrooms'));
        }

        if ($request->min_guests) {
            $query->where('max_guests', '>=', $request->integer('min_guests'));
        }

        if ($request->amenities) {
            foreach ((array) $request->amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        if ($request->check_in && $request->check_out) {
            $query->whereDoesntHave('blockedDates', fn ($q) => $q
                ->where('start_date', '<=', $request->check_out)
                ->where('end_date', '>=', $request->check_in));
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('price_per_night'),
            'price_desc' => $query->orderByDesc('price_per_night'),
            'name_asc' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))"),
            default => $query->orderByDesc('featured')->latest(),
        };

        return Inertia::render('Shop', [
            'accommodations' => $query->paginate(12)->withQueryString(),
            'categories' => Category::forTheme('Bookings')->get(),
            'filters' => $request->only(['category', 'search', 'location', 'max_price', 'min_bedrooms', 'min_guests', 'amenities', 'check_in', 'check_out', 'sort']),
        ]);
    }

    public function show(Accommodation $accommodation): Response
    {
        $accommodation->load(['category', 'reviews.user', 'blockedDates']);
        $accommodation->loadAvg('reviews', 'rating');
        $accommodation->loadCount('reviews');

        $recommended = Accommodation::with(['category', 'media'])
            ->recommended($accommodation)
            ->get();

        $blockedDates = $accommodation->blockedDates
            ->map(fn ($blocked) => [
                'start_date' => $blocked->start_date->toDateString(),
                'end_date' => $blocked->end_date->toDateString(),
            ])
            ->values()
            ->all();

        $settings = app(GeneralSettings::class);
        $title = is_array($accommodation->title) ? ($accommodation->title['en'] ?? '') : (string) $accommodation->title;

        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $title,
            'description' => is_array($accommodation->short_description) ? ($accommodation->short_description['en'] ?? '') : (string) ($accommodation->short_description ?? ''),
            'image' => $accommodation->image_url,
            'url' => route('product.show', $accommodation),
            'address' => $accommodation->location ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $accommodation->location,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $accommodation->price_per_night,
                'priceCurrency' => $settings->currency ?? 'EUR',
            ],
            'aggregateRating' => $accommodation->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $accommodation->reviews_avg_rating, 1),
                'reviewCount' => $accommodation->reviews_count,
            ] : null,
        ]);

        return Inertia::render('Product', [
            'accommodation' => $accommodation,
            'recommended' => $recommended,
            'blockedDates' => $blockedDates,
            'schema' => $schema,
        ]);
    }
}
