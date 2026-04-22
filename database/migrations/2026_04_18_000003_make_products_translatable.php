<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('name_new')->nullable()->after('name');
            $table->json('short_description_new')->nullable()->after('short_description');
            $table->json('description_new')->nullable()->after('description');
        });

        DB::table('products')->get()->each(function ($row) {
            DB::table('products')->where('id', $row->id)->update([
                'name_new' => json_encode(['en' => $row->name]),
                'short_description_new' => json_encode(['en' => $row->short_description]),
                'description_new' => json_encode(['en' => $row->description]),
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'short_description', 'description']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name_new', 'name');
            $table->renameColumn('short_description_new', 'short_description');
            $table->renameColumn('description_new', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_old')->nullable()->after('name');
            $table->text('short_description_old')->nullable()->after('short_description');
            $table->longText('description_old')->nullable()->after('description');
        });

        DB::table('products')->get()->each(function ($row) {
            $name = json_decode($row->name, true)['en'] ?? '';
            $shortDescription = json_decode($row->short_description, true)['en'] ?? '';
            $description = json_decode($row->description, true)['en'] ?? '';
            DB::table('products')->where('id', $row->id)->update([
                'name_old' => $name,
                'short_description_old' => $shortDescription,
                'description_old' => $description,
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'short_description', 'description']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name_old', 'name');
            $table->renameColumn('short_description_old', 'short_description');
            $table->renameColumn('description_old', 'description');
        });
    }
};
