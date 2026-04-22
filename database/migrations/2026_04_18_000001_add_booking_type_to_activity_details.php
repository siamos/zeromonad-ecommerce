<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_details', function (Blueprint $table) {
            $table->enum('booking_type', ['activity', 'accommodation', 'vehicle', 'tour', 'event'])
                ->default('activity')
                ->after('product_id');
            $table->json('extra_attributes')->nullable()->after('booking_cutoff_hours');
        });
    }

    public function down(): void
    {
        Schema::table('activity_details', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'extra_attributes']);
        });
    }
};
