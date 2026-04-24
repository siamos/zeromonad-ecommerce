<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $palettes = [
            'products_palette' => '"indigo"',
            'activities_palette' => '"emerald"',
            'bookings_palette' => '"amber"',
            'cars_palette' => '"slate"',
        ];

        foreach ($palettes as $name => $payload) {
            $exists = DB::table('settings')
                ->where('group', 'general')
                ->where('name', $name)
                ->exists();

            if (! $exists) {
                DB::table('settings')->insert([
                    'group' => 'general',
                    'name' => $name,
                    'locked' => false,
                    'payload' => $payload,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'general')
            ->whereIn('name', ['products_palette', 'activities_palette', 'bookings_palette', 'cars_palette'])
            ->delete();
    }
};
