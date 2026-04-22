<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->json('title_new')->nullable()->after('title');
            $table->json('excerpt_new')->nullable()->after('excerpt');
            $table->json('content_new')->nullable()->after('content');
        });

        DB::table('blog_posts')->get()->each(function ($row) {
            DB::table('blog_posts')->where('id', $row->id)->update([
                'title_new' => json_encode(['en' => $row->title]),
                'excerpt_new' => json_encode(['en' => $row->excerpt]),
                'content_new' => json_encode(['en' => $row->content]),
            ]);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'content']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->renameColumn('title_new', 'title');
            $table->renameColumn('excerpt_new', 'excerpt');
            $table->renameColumn('content_new', 'content');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title_old')->nullable()->after('title');
            $table->text('excerpt_old')->nullable()->after('excerpt');
            $table->longText('content_old')->nullable()->after('content');
        });

        DB::table('blog_posts')->get()->each(function ($row) {
            $title = json_decode($row->title, true)['en'] ?? '';
            $excerpt = json_decode($row->excerpt, true)['en'] ?? '';
            $content = json_decode($row->content, true)['en'] ?? '';
            DB::table('blog_posts')->where('id', $row->id)->update([
                'title_old' => $title,
                'excerpt_old' => $excerpt,
                'content_old' => $content,
            ]);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'content']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->renameColumn('title_old', 'title');
            $table->renameColumn('excerpt_old', 'excerpt');
            $table->renameColumn('content_old', 'content');
        });
    }
};
