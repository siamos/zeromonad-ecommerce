<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = BlogPost::where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(BlogPost $post): Response
    {
        abort_if($post->status !== 'published', 404);

        return Inertia::render('Blog/Show', [
            'post' => $post,
        ]);
    }
}
