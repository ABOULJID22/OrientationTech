<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $column = collect(DB::select("
            SELECT DATA_TYPE, COLUMN_TYPE, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'notifications'
                AND COLUMN_NAME = 'id'
            LIMIT 1
        "))->first();

        if (! $column) {
            return;
        }

        $dataType = strtolower((string) $column->DATA_TYPE);
        $extra = strtolower((string) $column->EXTRA);

        if (in_array($dataType, ['char', 'varchar'], true) && ! str_contains($extra, 'auto_increment')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE notifications MODIFY id BIGINT UNSIGNED NOT NULL');
        } catch (Throwable) {
            // The column may already be non-incrementing on some installations.
        }

        try {
            DB::statement('ALTER TABLE notifications DROP PRIMARY KEY');
        } catch (Throwable) {
            // Some legacy schemas may not have the expected primary key.
        }

        DB::statement('ALTER TABLE notifications MODIFY id CHAR(36) NOT NULL');

        try {
            DB::statement('ALTER TABLE notifications ADD PRIMARY KEY (id)');
        } catch (Throwable) {
            // Keep the migration idempotent if a primary key already exists.
        }
    }

    public function down(): void
    {
        // UUID notification IDs cannot be safely converted back to integers.
    }
};
