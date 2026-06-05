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

        if (Schema::hasColumn('notifications', 'title')) {
            DB::statement('ALTER TABLE notifications MODIFY title VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('notifications', 'message')) {
            DB::statement('ALTER TABLE notifications MODIFY message TEXT NULL');
        }

        if (Schema::hasColumn('notifications', 'user_id')) {
            DB::statement('ALTER TABLE notifications MODIFY user_id CHAR(36) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('notifications', 'title')) {
            DB::statement("UPDATE notifications SET title = JSON_UNQUOTE(JSON_EXTRACT(data, '$.title')) WHERE title IS NULL AND data IS NOT NULL");
            DB::statement("UPDATE notifications SET title = 'Notification' WHERE title IS NULL");
            DB::statement('ALTER TABLE notifications MODIFY title VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('notifications', 'message')) {
            DB::statement("UPDATE notifications SET message = JSON_UNQUOTE(JSON_EXTRACT(data, '$.body')) WHERE message IS NULL AND data IS NOT NULL");
            DB::statement("UPDATE notifications SET message = '' WHERE message IS NULL");
            DB::statement('ALTER TABLE notifications MODIFY message TEXT NOT NULL');
        }
    }
};
