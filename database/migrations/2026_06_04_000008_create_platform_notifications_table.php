<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('created_by')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('type', 30)->default('info')->index();
            $table->string('feature_key', 80)->default('general')->index();
            $table->json('target_roles')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedInteger('sent_count')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_notifications');
    }
};
