<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookup_values', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->string('value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category', 'value']);
        });

        $now = now();
        DB::table('lookup_values')->insert([
            ['category' => 'program', 'value' => 'Home Programme', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'program', 'value' => 'Center Meruya', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'program', 'value' => 'Center Interkota', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_values');
    }
};
