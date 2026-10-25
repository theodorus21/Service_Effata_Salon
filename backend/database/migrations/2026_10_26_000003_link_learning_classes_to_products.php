<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_classes', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('name')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('price')->default(0)->after('product_id');
        });

        $this->backfillProducts();

        Schema::table('learning_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
            $table->dropConstrainedForeignId('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('learning_classes', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('teacher_id')->constrained('lookup_values')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->after('program_id')->constrained('lookup_values')->restrictOnDelete();
        });

        DB::table('learning_classes')
            ->join('products', 'products.id', '=', 'learning_classes.product_id')
            ->update([
                'learning_classes.program_id' => DB::raw('products.program_id'),
                'learning_classes.category_id' => DB::raw('products.category_id'),
            ]);

        Schema::table('learning_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('price');
        });
    }

    /**
     * Existing classes keep their program and category through a legacy product per combination.
     */
    private function backfillProducts(): void
    {
        $classes = DB::table('learning_classes')->orderBy('id')->get();
        if ($classes->isEmpty()) {
            return;
        }

        $now = now();
        $lookupId = fn (string $category) => DB::table('lookup_values')->where(['category' => $category, 'value' => 'Umum'])->value('id')
            ?? DB::table('lookup_values')->insertGetId([
                'category' => $category,
                'value' => 'Umum',
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        $levelId = $lookupId('class_level');
        $frequencyId = $lookupId('class_frequency');

        $productIds = [];
        foreach ($classes as $class) {
            $key = $class->program_id.'-'.$class->category_id;
            $productIds[$key] ??= DB::table('products')->insertGetId([
                'name' => $class->name,
                'code' => 'LEGACY-'.(count($productIds) + 1),
                'level_id' => $levelId,
                'program_id' => $class->program_id,
                'category_id' => $class->category_id,
                'frequency_id' => $frequencyId,
                'duration_minutes' => 60,
                'price' => 0,
                'description' => 'Dibuat otomatis dari kelas yang sudah ada.',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('learning_classes')->where('id', $class->id)->update(['product_id' => $productIds[$key]]);
        }
    }
};
