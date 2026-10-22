<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('lookup_values')->insertOrIgnore([
            [
                'category' => 'class_category',
                'value' => 'Pelajaran',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 'class_category',
                'value' => 'Musik',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('learning_classes', function (Blueprint $table): void {
            $table->foreignId('category_id')
                ->nullable()
                ->after('program_id')
                ->constrained('lookup_values')
                ->restrictOnDelete();
        });

        $defaultCategoryId = DB::table('lookup_values')
            ->where('category', 'class_category')
            ->where('value', 'Pelajaran')
            ->value('id');

        DB::table('learning_classes')
            ->whereNull('category_id')
            ->update(['category_id' => $defaultCategoryId]);
    }

    public function down(): void
    {
        Schema::table('learning_classes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
