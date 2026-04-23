<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::where('status', 'published')
            ->select(['slug', 'updated_at'])
            ->latest('updated_at')
            ->get();

        $posts = BlogPost::where('status', 'published')
            ->select(['slug', 'updated_at'])
            ->latest('updated_at')
            ->get();

        $staticUrls = [
            ['url' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['url' => route('shop'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['url' => route('blog.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
        ];

        $xml = view('sitemap', compact('products', 'posts', 'staticUrls'));

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
