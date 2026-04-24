<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\RentalLocation;
use App\Models\Vehicle;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function home(): Response
    {
        $featured = Vehicle::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::forTheme('Cars')->get();

        return Inertia::render('Home', [
            'featuredVehicles' => $featured,
            'categories' => $categories,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Vehicle::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'published');

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->vehicle_type) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        if ($request->transmission) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->min_seats) {
            $query->where('seats', '>=', $request->integer('min_seats'));
        }

        if ($request->max_price) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        if ($request->pickup_location) {
            $query->where('pickup_location', 'like', '%'.$request->pickup_location.'%');
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('price_per_day'),
            'price_desc' => $query->orderByDesc('price_per_day'),
            'name_asc' => $query->orderBy('make')->orderBy('model'),
            default => $query->orderByDesc('featured')->latest(),
        };

        return Inertia::render('Shop', [
            'vehicles' => $query->paginate(12)->withQueryString(),
            'categories' => Category::forTheme('Cars')->get(),
            'filters' => $request->only(['category', 'search', 'vehicle_type', 'transmission', 'min_seats', 'max_price', 'pickup_location', 'sort']),
        ]);
    }

    public function show(Vehicle $vehicle): Response
    {
        $vehicle->load(['category', 'reviews.user']);
        $vehicle->loadAvg('reviews', 'rating');
        $vehicle->loadCount('reviews');

        $recommended = Vehicle::with(['category', 'media'])
            ->recommended($vehicle)
            ->get();

        $today = now()->toDateString();

        $bookedRanges = OrderItem::query()
            ->where('orderable_type', 'vehicle')
            ->where('orderable_id', $vehicle->id)
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled']))
            ->whereNotNull('options->pickup_date')
            ->whereNotNull('options->return_date')
            ->where('options->return_date', '>=', $today)
            ->get(['options'])
            ->map(fn ($item) => [
                'from' => $item->options['pickup_date'] ?? null,
                'to' => $item->options['return_date'] ?? null,
            ])
            ->filter(fn ($r) => $r['from'] && $r['to'])
            ->values();

        $isAvailable = $vehicle->is_available && $bookedRanges->isEmpty();

        $pickupLocations = RentalLocation::forPickup()
            ->get(['id', 'name', 'address', 'type', 'pickup_fee']);

        $dropoffLocations = RentalLocation::forDropoff()
            ->get(['id', 'name', 'address', 'type', 'dropoff_fee']);

        $settings = app(GeneralSettings::class);
        $vehicleName = "{$vehicle->make} {$vehicle->model} {$vehicle->year}";

        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $vehicleName,
            'description' => is_array($vehicle->short_description) ? ($vehicle->short_description['en'] ?? '') : (string) ($vehicle->short_description ?? ''),
            'image' => $vehicle->image_url,
            'url' => route('product.show', $vehicle),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $vehicle->price_per_day,
                'priceCurrency' => $settings->currency ?? 'EUR',
                'availability' => $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
            'aggregateRating' => $vehicle->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $vehicle->reviews_avg_rating, 1),
                'reviewCount' => $vehicle->reviews_count,
            ] : null,
        ]);

        return Inertia::render('Product', [
            'vehicle' => $vehicle,
            'recommended' => $recommended,
            'isAvailable' => $isAvailable,
            'bookedRanges' => $bookedRanges,
            'pickupLocations' => $pickupLocations,
            'dropoffLocations' => $dropoffLocations,
            'schema' => $schema,
        ]);
    }
}
