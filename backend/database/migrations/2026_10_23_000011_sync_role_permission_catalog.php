<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissionKeys = [];

        foreach (config('role_permissions') as $groupKey => $group) {
            foreach ($group['permissions'] as $key => $label) {
                $permissionKeys[] = $key;
                DB::table('permissions')->insertOrIgnore([
                    'key' => $key,
                    'group_key' => $groupKey,
                    'group_label' => $group['label'],
                    'label' => $label,
                    'is_menu' => str_ends_with($key, '.view'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $masterAdminCode = DB::table('roles')
            ->where('code', 'master_admin')
            ->value('code');

        if ($masterAdminCode) {
            foreach ($permissionKeys as $permissionKey) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_code' => $masterAdminCode,
                    'permission_key' => $permissionKey,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionKeys = [
            'settings.programs.view',
            'settings.categories.view',
            'settings.lesson_statuses.view',
        ];

        DB::table('role_permissions')->whereIn('permission_key', $permissionKeys)->delete();
        DB::table('permissions')->whereIn('key', $permissionKeys)->delete();
    }
};
