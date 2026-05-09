<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->boolean('is_suspicious')->default(false);
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'logged_in_at']);
            $table->index(['ip_address', 'is_suspicious']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('login_logs');
    }
};
