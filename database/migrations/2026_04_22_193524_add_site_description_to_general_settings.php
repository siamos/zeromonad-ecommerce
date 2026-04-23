<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'site_description')
            ->exists();

        if (! $exists) {
            DB::table('settings')->insert([
                'group' => 'general',
                'name' => 'site_description',
                'locked' => false,
                'payload' => 'null',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'site_description')
            ->delete();
    }
};
