<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('cartable_type')->nullable()->after('product_id');
            $table->unsignedBigInteger('cartable_id')->nullable()->after('cartable_type');
            $table->index(['cartable_type', 'cartable_id']);
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['cartable_type', 'cartable_id']);
            $table->dropColumn(['cartable_type', 'cartable_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
