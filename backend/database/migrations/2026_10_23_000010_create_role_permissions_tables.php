<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->string('key', 120)->primary();
            $table->string('group_key', 80);
            $table->string('group_label', 120);
            $table->string('label', 180);
            $table->boolean('is_menu')->default(false);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_code', 100);
            $table->string('permission_key', 120);
            $table->timestamps();
            $table->unique(['role_code', 'permission_key']);
            $table->foreign('role_code')->references('code')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_key')->references('key')->on('permissions')->cascadeOnDelete();
        });

        $now = now();
        $allKeys = [];
        foreach (config('role_permissions') as $groupKey => $group) {
            foreach ($group['permissions'] as $key => $label) {
                $allKeys[] = $key;
                DB::table('permissions')->insert([
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

        $masterAdmin = Role::query()->where('code', 'master_admin')->first();
        if ($masterAdmin) {
            foreach ($allKeys as $key) {
                DB::table('role_permissions')->insert([
                    'role_code' => $masterAdmin->code,
                    'permission_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (Role::query()->where('code', '!=', 'master_admin')->pluck('code') as $roleCode) {
            DB::table('role_permissions')->insert([
                'role_code' => $roleCode,
                'permission_key' => 'dashboard.view',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
