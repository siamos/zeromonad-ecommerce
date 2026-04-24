<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('reviewable_type')->nullable()->after('product_id');
            $table->unsignedBigInteger('reviewable_id')->nullable()->after('reviewable_type');
            $table->index(['reviewable_type', 'reviewable_id']);
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['reviewable_type', 'reviewable_id']);
            $table->dropColumn(['reviewable_type', 'reviewable_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
