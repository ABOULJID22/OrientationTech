<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_diagnostics', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_diagnostics', 'interest_theme')) {
                $table->string('interest_theme')->nullable();
            }

            if (! Schema::hasColumn('academic_diagnostics', 'remark')) {
                $table->text('remark')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_diagnostics', function (Blueprint $table) {
            if (Schema::hasColumn('academic_diagnostics', 'interest_theme')) {
                $table->dropColumn('interest_theme');
            }

            if (Schema::hasColumn('academic_diagnostics', 'remark')) {
                $table->dropColumn('remark');
            }
        });
    }
};
