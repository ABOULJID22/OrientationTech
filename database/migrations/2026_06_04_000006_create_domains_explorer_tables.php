<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable()->index();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->longText('why_important')->nullable();
            $table->json('student_profile')->nullable();
            $table->json('school_subjects')->nullable();
            $table->json('technical_skills')->nullable();
            $table->json('soft_skills')->nullable();
            $table->json('tools')->nullable();
            $table->json('related_jobs')->nullable();
            $table->json('learning_path')->nullable();
            $table->json('schools_morocco')->nullable();
            $table->json('certifications')->nullable();
            $table->string('global_demand')->nullable();
            $table->string('morocco_demand')->nullable();
            $table->string('difficulty_level')->nullable()->index();
            $table->string('future_potential')->nullable()->index();
            $table->string('ai_impact')->nullable()->index();
            $table->unsignedTinyInteger('freelance_opportunity')->default(0)->index();
            $table->unsignedTinyInteger('remote_opportunity')->default(0)->index();
            $table->unsignedTinyInteger('math_score')->default(0);
            $table->unsignedTinyInteger('creativity_score')->default(0);
            $table->unsignedTinyInteger('communication_score')->default(0);
            $table->unsignedTinyInteger('problem_solving_score')->default(0);
            $table->unsignedInteger('junior_salary_min')->nullable();
            $table->unsignedInteger('junior_salary_max')->nullable();
            $table->unsignedInteger('senior_salary_min')->nullable();
            $table->unsignedInteger('senior_salary_max')->nullable();
            $table->string('currency', 12)->default('MAD');
            $table->text('salary_note')->nullable();
            $table->json('advantages')->nullable();
            $table->json('challenges')->nullable();
            $table->text('start_tips')->nullable();
            $table->json('practical_projects')->nullable();
            $table->text('keywords')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'category']);
            $table->index(['likes_count', 'views_count']);
        });

        Schema::create('domain_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('session_id')->nullable();
            $table->dateTime('viewed_at')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['domain_id', 'user_id', 'viewed_at']);
            $table->index(['domain_id', 'session_id', 'viewed_at']);
            $table->index(['domain_id', 'ip_hash', 'viewed_at']);
        });

        Schema::create('domain_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['domain_id', 'user_id']);
        });

        Schema::create('domain_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['domain_id', 'user_id']);
        });

        Schema::create('domain_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['domain_id', 'user_id']);
        });

        Schema::create('domain_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->foreignId('parent_id')->nullable()->constrained('domain_comments')->cascadeOnDelete();
            $table->text('content');
            $table->string('status', 30)->default('visible')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['domain_id', 'status', 'created_at']);
        });

        Schema::create('domain_comment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('domain_comments')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->string('reason', 120);
            $table->text('details')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['comment_id', 'user_id']);
        });

        Schema::create('domain_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->uuid('teacher_id');
            $table->string('suggestion_type', 80);
            $table->longText('content');
            $table->string('status', 30)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_suggestions');
        Schema::dropIfExists('domain_comment_reports');
        Schema::dropIfExists('domain_comments');
        Schema::dropIfExists('domain_ratings');
        Schema::dropIfExists('domain_favorites');
        Schema::dropIfExists('domain_likes');
        Schema::dropIfExists('domain_views');
        Schema::dropIfExists('domains');
    }
};
