<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('frequency_id')->nullable()->after('category_id')
                ->constrained('lookup_values')->restrictOnDelete();
        });

        $now = now();
        foreach (config('role_permissions.settings.permissions') as $key => $label) {
            if (! str_starts_with($key, 'settings.frequencies.')) {
                continue;
            }

            DB::table('permissions')->insertOrIgnore([
                'key' => $key,
                'group_key' => 'settings',
                'group_label' => config('role_permissions.settings.label'),
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
        $keys = array_filter(
            array_keys(config('role_permissions.settings.permissions')),
            fn ($key) => str_starts_with($key, 'settings.frequencies.'),
        );
        DB::table('role_permissions')->whereIn('permission_key', $keys)->delete();
        DB::table('permissions')->whereIn('key', $keys)->delete();

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('frequency_id');
        });
    }
};
