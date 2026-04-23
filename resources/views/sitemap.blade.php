<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($staticUrls as $entry)
    <url>
        <loc>{{ $entry['url'] }}</loc>
        <changefreq>{{ $entry['changefreq'] }}</changefreq>
        <priority>{{ $entry['priority'] }}</priority>
    </url>
    @endforeach

    @foreach($products as $product)
    <url>
        <loc>{{ route('product.show', $product->slug) }}</loc>
        <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    @foreach($posts as $post)
    <url>
        <loc>{{ route('blog.show', $post->slug) }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
