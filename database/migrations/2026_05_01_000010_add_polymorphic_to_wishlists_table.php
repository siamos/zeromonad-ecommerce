<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->string('wishable_type')->nullable()->after('product_id');
            $table->unsignedBigInteger('wishable_id')->nullable()->after('wishable_type');
            $table->index(['wishable_type', 'wishable_id']);
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex(['wishable_type', 'wishable_id']);
            $table->dropColumn(['wishable_type', 'wishable_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
