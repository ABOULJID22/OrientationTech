<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('teacher_id')->nullable();
            $table->string('type', 40)->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('domain_key')->nullable();
            $table->string('career_name')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->dateTime('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_contents');
    }
};
