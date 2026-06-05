<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->dateTime('viewed_at')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['post_id', 'user_id', 'viewed_at']);
            $table->index(['post_id', 'session_id', 'viewed_at']);
            $table->index(['post_id', 'ip_hash', 'viewed_at']);
        });

        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->foreignId('parent_id')->nullable()->constrained('post_comments')->cascadeOnDelete();
            $table->text('content');
            $table->string('status', 30)->default('visible')->index();
            $table->uuid('hidden_by')->nullable();
            $table->dateTime('hidden_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('hidden_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['post_id', 'status', 'created_at']);
            $table->index(['parent_id', 'status']);
        });

        Schema::create('post_comment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_comment_id')->constrained('post_comments')->cascadeOnDelete();
            $table->uuid('reporter_id');
            $table->string('reason', 120);
            $table->text('details')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->uuid('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('reporter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['post_comment_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comment_reports');
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('post_views');
    }
};
