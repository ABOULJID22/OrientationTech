<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('user_type');
            $table->string('user_other')->nullable();
            $table->text('message');
            $table->text('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->uuid('replied_by')->nullable();
            $table->foreign('replied_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('contacts');
    }
};
