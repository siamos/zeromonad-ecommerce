<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->dateTime('event_date');
            $table->string('location');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('booking_cutoff_hours')->default(24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_details');
    }
};
