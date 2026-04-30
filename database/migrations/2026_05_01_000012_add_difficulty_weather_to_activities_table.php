<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'moderate', 'hard', 'expert'])->nullable()->after('booking_cutoff_hours');
            $table->unsignedSmallInteger('min_age')->nullable()->after('difficulty');
            $table->boolean('weather_dependent')->default(false)->after('min_age');
            $table->text('cancellation_policy')->nullable()->after('weather_dependent');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'min_age', 'weather_dependent', 'cancellation_policy']);
        });
    }
};
