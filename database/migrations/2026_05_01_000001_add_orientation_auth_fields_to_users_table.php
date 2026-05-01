<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('pending');
            }
            if (! Schema::hasColumn('users', 'school_level')) {
                $table->string('school_level')->nullable();
            }
            if (! Schema::hasColumn('users', 'specialty')) {
                $table->string('specialty')->nullable();
            }
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable();
            }
            if (! Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->text('last_login_user_agent')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            foreach (['role', 'status', 'school_level', 'specialty', 'last_login_ip', 'last_login_user_agent'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
