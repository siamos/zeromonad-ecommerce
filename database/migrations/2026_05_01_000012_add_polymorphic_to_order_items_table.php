<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('orderable_type')->nullable()->after('product_id');
            $table->unsignedBigInteger('orderable_id')->nullable()->after('orderable_type');
            $table->index(['orderable_type', 'orderable_id']);
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['orderable_type', 'orderable_id']);
            $table->dropColumn(['orderable_type', 'orderable_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
