<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = collect(config('role_permissions'))
            ->flatMap(fn (array $group) => $group['permissions'])
            ->get('users.api.manage');

        if ($permission === null) {
            return;
        }

        $now = now();
        DB::table('permissions')->insertOrIgnore([
            'key' => 'users.api.manage',
            'group_key' => 'master_data',
            'group_label' => config('role_permissions.master_data.label'),
            'label' => $permission,
            'is_menu' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (DB::table('roles')->where('code', 'master_admin')->exists()) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_code' => 'master_admin',
                'permission_key' => 'users.api.manage',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('permission_key', 'users.api.manage')->delete();
        DB::table('permissions')->where('key', 'users.api.manage')->delete();
    }
};
