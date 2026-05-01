<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('post_tag')) {
            Schema::create('post_tag', function (Blueprint $table) {
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->primary(['post_id','tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};
