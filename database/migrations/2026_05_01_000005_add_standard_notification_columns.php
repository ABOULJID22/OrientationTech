<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable()->after('id');
            }
            if (! Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->string('notifiable_id')->nullable()->after('notifiable_type');
            }
            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable()->after('notifiable_id');
            }
            if (! Schema::hasColumn('notifications', 'data')) {
                // Use JSON where supported, fallback to longText if not
                if (Schema::getConnection()->getDriverName() === 'mysql') {
                    $table->json('data')->nullable()->after('type');
                } else {
                    $table->longText('data')->nullable()->after('type');
                }
            }
            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('data');
            }

            // Add index for notifiable lookup
            if (! Schema::hasColumn('notifications', 'notifiable_type') || ! Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_type_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->dropColumn('notifiable_type');
            }
            if (Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->dropColumn('notifiable_id');
            }
            if (Schema::hasColumn('notifications', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'read_at')) {
                // keep existing read_at if originally present; only drop if it was added by this migration
                // no reliable way to detect origin, so skip dropping read_at to avoid data loss
            }
            // drop index if exists
            try {
                $table->dropIndex('notifications_notifiable_type_id_index');
            } catch (\Throwable $e) {
                // ignore if index does not exist
            }
        });
    }
};
