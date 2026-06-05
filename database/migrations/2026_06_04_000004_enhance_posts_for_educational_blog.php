<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('views_count');
            }

            if (! Schema::hasColumn('posts', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            }

            if (! Schema::hasColumn('posts', 'approved_by')) {
                $table->uuid('approved_by')->nullable()->after('published_at');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('posts', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('posts', 'rejected_at')) {
                $table->dateTime('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('posts', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `posts` MODIFY `status` ENUM('draft','pending','scheduled','published','rejected','archived') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `posts` MODIFY `status` ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }

            foreach (['likes_count', 'comments_count', 'approved_by', 'approved_at', 'rejected_at', 'rejection_reason'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
