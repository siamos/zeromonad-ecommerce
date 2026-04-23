<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim($request->string('q'));

        if (strlen($query) < 2) {
            return response()->json(['products' => [], 'posts' => []]);
        }

        $products = Product::search($query)
            ->query(fn ($q) => $q->where('status', 'published')->with('media'))
            ->take(6)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'image_url' => $p->image_url,
            ]);

        $posts = BlogPost::search($query)
            ->query(fn ($q) => $q->published())
            ->take(3)
            ->get()
            ->map(fn (BlogPost $post) => [
                'slug' => $post->slug,
                'title' => $post->title,
            ]);

        return response()->json(compact('products', 'posts'));
    }
}
