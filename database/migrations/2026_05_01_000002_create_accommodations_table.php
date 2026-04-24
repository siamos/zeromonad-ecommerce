<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->tinyInteger('bedrooms')->unsigned()->default(1);
            $table->tinyInteger('bathrooms')->unsigned()->default(1);
            $table->tinyInteger('max_guests')->unsigned()->default(2);
            $table->json('amenities')->nullable();
            $table->json('house_rules')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
