<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->foreignId('level_id')->constrained('lookup_values')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('lookup_values')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('lookup_values')->restrictOnDelete();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedBigInteger('price');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();
        $groups = [
            'products' => config('role_permissions.products'),
            'settings' => ['label' => config('role_permissions.settings.label'), 'permissions' => array_filter(
                config('role_permissions.settings.permissions'),
                fn ($label, $key) => str_starts_with($key, 'settings.levels.'),
                ARRAY_FILTER_USE_BOTH,
            )],
        ];

        foreach ($groups as $groupKey => $group) {
            foreach ($group['permissions'] as $key => $label) {
                DB::table('permissions')->insertOrIgnore([
                    'key' => $key,
                    'group_key' => $groupKey,
                    'group_label' => $group['label'],
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
    }

    public function down(): void
    {
        $keys = array_merge(
            array_keys(config('role_permissions.products.permissions')),
            array_filter(
                array_keys(config('role_permissions.settings.permissions')),
                fn ($key) => str_starts_with($key, 'settings.levels.'),
            ),
        );
        DB::table('role_permissions')->whereIn('permission_key', $keys)->delete();
        DB::table('permissions')->whereIn('key', $keys)->delete();

        Schema::dropIfExists('products');
    }
};
