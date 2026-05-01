<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // No-op: Video fields now added in 2026_05_01_000004_add_video_fields_to_site_settings_table
    }

    public function down(): void {
        // No-op
    }
};

