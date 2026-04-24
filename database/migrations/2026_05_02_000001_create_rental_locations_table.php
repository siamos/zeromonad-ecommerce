<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->enum('type', ['branch', 'delivery'])->default('branch');
            $table->boolean('pickup_available')->default(true);
            $table->boolean('dropoff_available')->default(true);
            $table->decimal('pickup_fee', 8, 2)->default(0);
            $table->decimal('dropoff_fee', 8, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_locations');
    }
};
