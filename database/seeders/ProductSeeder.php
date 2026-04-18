<?php

namespace Database\Seeders;

use App\Models\ActivityDetail;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Product categories
        $electronics = Category::firstOrCreate(['slug' => 'electronics'], [
            'name' => 'Electronics',
            'description' => 'Gadgets and devices',
        ]);
        $clothing = Category::firstOrCreate(['slug' => 'clothing'], [
            'name' => 'Clothing',
            'description' => 'Fashion and apparel',
        ]);
        $books = Category::firstOrCreate(['slug' => 'books'], [
            'name' => 'Books',
            'description' => 'Fiction and non-fiction',
        ]);

        // Activity categories
        $tours = Category::firstOrCreate(['slug' => 'tours'], [
            'name' => 'Tours',
            'description' => 'Guided sightseeing experiences',
        ]);
        $classes = Category::firstOrCreate(['slug' => 'classes'], [
            'name' => 'Classes',
            'description' => 'Workshops and learning experiences',
        ]);
        $adventures = Category::firstOrCreate(['slug' => 'adventures'], [
            'name' => 'Adventures',
            'description' => 'Outdoor and thrill activities',
        ]);

        // Sample products
        $products = [
            ['name' => 'Wireless Headphones', 'price' => 89.99, 'stock' => 50, 'category_id' => $electronics->id, 'featured' => true],
            ['name' => 'Mechanical Keyboard', 'price' => 129.00, 'stock' => 25, 'category_id' => $electronics->id, 'featured' => false],
            ['name' => 'Organic Cotton T-Shirt', 'price' => 29.99, 'stock' => 100, 'category_id' => $clothing->id, 'featured' => true],
            ['name' => 'Denim Jacket', 'price' => 79.99, 'stock' => 30, 'category_id' => $clothing->id, 'featured' => false],
            ['name' => 'Clean Code', 'price' => 34.99, 'stock' => 40, 'category_id' => $books->id, 'featured' => false],
            ['name' => 'The Pragmatic Programmer', 'price' => 39.99, 'stock' => 20, 'category_id' => $books->id, 'featured' => true],
        ];

        foreach ($products as $data) {
            $data['slug']        = str($data['name'])->slug();
            $data['status']      = 'published';
            $data['description'] = 'A high-quality product that exceeds expectations.';
            $data['short_description'] = 'Premium quality. Fast shipping.';
            Product::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // Sample activities
        $activities = [
            [
                'name'        => 'Athens Food Walking Tour',
                'price'       => 45.00,
                'stock'       => 20,
                'category_id' => $tours->id,
                'featured'    => true,
                'activity'    => [
                    'event_date'             => now()->addDays(7),
                    'location'              => 'Monastiraki Square, Athens',
                    'capacity'              => 15,
                    'duration_minutes'      => 180,
                    'booking_cutoff_hours'  => 24,
                ],
            ],
            [
                'name'        => 'Greek Cooking Masterclass',
                'price'       => 65.00,
                'stock'       => 12,
                'category_id' => $classes->id,
                'featured'    => true,
                'activity'    => [
                    'event_date'             => now()->addDays(14),
                    'location'              => 'Culinary Studio, Kolonaki',
                    'capacity'              => 10,
                    'duration_minutes'      => 240,
                    'booking_cutoff_hours'  => 48,
                ],
            ],
            [
                'name'        => 'Kayaking in the Aegean',
                'price'       => 85.00,
                'stock'       => 8,
                'category_id' => $adventures->id,
                'featured'    => true,
                'activity'    => [
                    'event_date'             => now()->addDays(21),
                    'location'              => 'Vouliagmeni Marina',
                    'capacity'              => 8,
                    'duration_minutes'      => 300,
                    'booking_cutoff_hours'  => 48,
                ],
            ],
            [
                'name'        => 'Photography Walk in Plaka',
                'price'       => 35.00,
                'stock'       => 15,
                'category_id' => $tours->id,
                'featured'    => false,
                'activity'    => [
                    'event_date'             => now()->addDays(10),
                    'location'              => 'Plaka District, Athens',
                    'capacity'              => 12,
                    'duration_minutes'      => 120,
                    'booking_cutoff_hours'  => 12,
                ],
            ],
        ];

        foreach ($activities as $data) {
            $activityData = $data['activity'];
            unset($data['activity']);

            $data['slug']              = str($data['name'])->slug();
            $data['status']            = 'published';
            $data['description']       = 'An unforgettable experience crafted for curious souls.';
            $data['short_description'] = 'Expert guide. Small groups. Memorable moments.';

            $product = Product::firstOrCreate(['slug' => $data['slug']], $data);

            ActivityDetail::firstOrCreate(
                ['product_id' => $product->id],
                $activityData
            );
        }
    }
}
