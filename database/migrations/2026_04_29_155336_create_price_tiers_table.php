<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->morphs('tierable');
            $table->unsignedInteger('min_quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->index(['tierable_type', 'tierable_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
