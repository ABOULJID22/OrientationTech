<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'favorites_count')) {
                $table->unsignedInteger('favorites_count')->default(0)->after('likes_count');
            }
        });

        Schema::create('post_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::table('resource_contents', function (Blueprint $table) {
            if (! Schema::hasColumn('resource_contents', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('views_count');
            }

            if (! Schema::hasColumn('resource_contents', 'favorites_count')) {
                $table->unsignedInteger('favorites_count')->default(0)->after('likes_count');
            }
        });

        Schema::create('resource_content_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_content_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['resource_content_id', 'user_id']);
        });

        Schema::create('resource_content_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_content_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['resource_content_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_content_favorites');
        Schema::dropIfExists('resource_content_likes');
        Schema::dropIfExists('post_favorites');

        Schema::table('resource_contents', function (Blueprint $table) {
            if (Schema::hasColumn('resource_contents', 'favorites_count')) {
                $table->dropColumn('favorites_count');
            }

            if (Schema::hasColumn('resource_contents', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'favorites_count')) {
                $table->dropColumn('favorites_count');
            }
        });
    }
};
