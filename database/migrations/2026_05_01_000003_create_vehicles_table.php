<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->smallInteger('year')->unsigned();
            $table->string('slug')->unique();
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->enum('vehicle_type', ['car', 'suv', 'van', 'motorcycle', 'truck'])->default('car');
            $table->enum('transmission', ['manual', 'automatic'])->default('automatic');
            $table->tinyInteger('seats')->unsigned()->default(5);
            $table->string('mileage_policy')->nullable();
            $table->string('fuel_policy')->nullable();
            $table->string('pickup_location')->nullable();
            $table->boolean('is_available')->default(true);
            $table->json('extras')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
