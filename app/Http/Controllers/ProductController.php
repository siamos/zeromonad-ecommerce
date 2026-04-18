<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function home(): Response
    {
        $featured   = Product::with(['category', 'activityDetail'])
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::withCount('products')->get();

        return Inertia::render('Home', [
            'featuredActivities' => $featured,
            'featuredProducts'   => $featured,
            'categories'         => $categories,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'activityDetail'])
            ->where('status', 'published');

        if ($request->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->date) {
            $query->whereHas('activityDetail', fn ($q) => $q->whereDate('event_date', $request->date));
        }

        match ($request->sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'date_asc'   => $query->join('activity_details', 'products.id', '=', 'activity_details.product_id')
                ->orderBy('activity_details.event_date'),
            'name_asc'   => $query->orderBy('name'),
            default      => $query->orderByDesc('featured')->latest(),
        };

        return Inertia::render('Shop', [
            'activities'  => $query->paginate(12)->withQueryString(),
            'products'    => $query->paginate(12)->withQueryString(),
            'categories'  => Category::withCount('products')->get(),
            'filters'     => $request->only(['category', 'search', 'max_price', 'date', 'sort']),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'activityDetail', 'tags', 'reviews.user']);

        $related = Product::with(['category', 'activityDetail'])
            ->where('status', 'published')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return Inertia::render('Product', [
            'activity' => $product,
            'product'  => $product,
            'related'  => $related,
        ]);
    }
}
