<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $student = Role::firstOrCreate(['name' => 'student']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Give super_admin all permissions
        $all = Permission::all();
        if ($all->isNotEmpty()) {
            $superAdmin->syncPermissions($all);
        }

        // Teacher: allow blog/resource management permissions if present.
        $allowedResources = ['post', 'category', 'tag', 'resource'];
        $prefixes = [
            'view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'restore', 'restore_any', 'replicate', 'reorder', 'force_delete', 'force_delete_any',
        ];

        $teacherPerms = Permission::query()
            ->where(function ($q) use ($allowedResources, $prefixes) {
                foreach ($allowedResources as $res) {
                    foreach ($prefixes as $p) {
                        $q->orWhere('name', $p . '_' . $res);
                    }
                }
            })
            ->pluck('name')
            ->all();

        if (! empty($teacherPerms)) {
            $teacher->syncPermissions($teacherPerms);
        }

        $studentPerms = Permission::query()
            ->whereIn('name', [
                'view_resource',
                'view_any_resource',
                'view_post',
                'view_any_post',
            ])
            ->pluck('name')
            ->all();

        if (! empty($studentPerms)) {
            $student->syncPermissions($studentPerms);
        }

        $user->syncPermissions([]);
    }
}
