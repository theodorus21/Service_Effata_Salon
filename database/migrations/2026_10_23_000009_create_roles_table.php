<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 100)->unique();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $roles = [
            'master_admin' => 'Master Admin',
            'owner' => 'Owner',
            'admin' => 'Admin',
            'guru' => 'Guru',
            'murid' => 'Murid',
            'user' => 'User',
        ];

        foreach (DB::table('users')->whereNotNull('role')->distinct()->pluck('role') as $code) {
            $roles[$code] ??= ucwords(str_replace(['_', '-'], ' ', $code));
        }

        $timestamp = now();
        foreach ($roles as $code => $name) {
            DB::table('roles')->insert([
                'code' => $code,
                'name' => $name,
                'is_system' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
