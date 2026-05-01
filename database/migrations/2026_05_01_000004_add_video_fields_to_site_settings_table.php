<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'bgvideo_url')) {
                $table->string('bgvideo_url')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'presentationvideo_url')) {
                $table->string('presentationvideo_url')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'bgvideo_url')) {
                $table->dropColumn('bgvideo_url');
            }
            if (Schema::hasColumn('site_settings', 'presentationvideo_url')) {
                $table->dropColumn('presentationvideo_url');
            }
        });
    }
};
