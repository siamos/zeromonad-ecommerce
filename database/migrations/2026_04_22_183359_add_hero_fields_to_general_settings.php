<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fields = ['hero_title', 'hero_subtitle', 'hero_image', 'site_logo'];

        foreach ($fields as $field) {
            $exists = DB::table('settings')
                ->where('group', 'general')
                ->where('name', $field)
                ->exists();

            if (! $exists) {
                DB::table('settings')->insert([
                    'group' => 'general',
                    'name' => $field,
                    'locked' => false,
                    'payload' => 'null',
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
            ->whereIn('name', ['hero_title', 'hero_subtitle', 'hero_image', 'site_logo'])
            ->delete();
    }
};
