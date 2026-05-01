<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('posts', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('cover_image');
            }
            if (!Schema::hasColumn('posts', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('status');
            }
            if (!Schema::hasColumn('posts', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('is_featured');
            }
            if (!Schema::hasColumn('posts', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('posts', 'seo_description')) {
                $table->string('seo_description')->nullable()->after('seo_title');
            }
        });

        // Update enum to include 'archived'
        DB::statement("ALTER TABLE `posts` MODIFY `status` ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft'");

        Schema::table('posts', function (Blueprint $table) {
            $table->index('is_featured');
            $table->index('views_count');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'excerpt')) {
                $table->dropColumn('excerpt');
            }
            if (Schema::hasColumn('posts', 'featured_image')) {
                $table->dropColumn('featured_image');
            }
            if (Schema::hasColumn('posts', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('posts', 'views_count')) {
                $table->dropColumn('views_count');
            }
            if (Schema::hasColumn('posts', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
            if (Schema::hasColumn('posts', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
        });

        // Revert enum (best-effort)
        DB::statement("ALTER TABLE `posts` MODIFY `status` ENUM('draft','scheduled','published') NOT NULL DEFAULT 'draft'");
    }
};
