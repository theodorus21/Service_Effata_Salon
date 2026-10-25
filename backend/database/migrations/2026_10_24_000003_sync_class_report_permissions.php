<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (config('role_permissions.class_reports.permissions') as $key => $label) {
            DB::table('permissions')->insertOrIgnore([
                'key' => $key,
                'group_key' => 'class_reports',
                'group_label' => config('role_permissions.class_reports.label'),
                'label' => $label,
                'is_menu' => str_ends_with($key, '.view'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (DB::table('roles')->where('code', 'master_admin')->exists()) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_code' => 'master_admin',
                    'permission_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $keys = array_keys(config('role_permissions.class_reports.permissions'));
        DB::table('role_permissions')->whereIn('permission_key', $keys)->delete();
        DB::table('permissions')->whereIn('key', $keys)->delete();
    }
};
