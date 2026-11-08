<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_class_students', function (Blueprint $table): void {
            $table->foreignId('learning_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['learning_class_id', 'student_id']);
            $table->index('student_id');
        });

        DB::table('learning_classes')
            ->select('id', 'student_id')
            ->chunkById(500, function ($classes): void {
                DB::table('learning_class_students')->insertOrIgnore(
                    $classes->map(fn (object $class): array => [
                        'learning_class_id' => $class->id,
                        'student_id' => $class->student_id,
                    ])->all(),
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_class_students');
    }
};
