<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedInteger('booked_count')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_slots');
    }
};
