<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_class_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_class_id')
                ->constrained('learning_classes')
                ->cascadeOnDelete();
            $table->date('lesson_date');
            $table->time('lesson_time');
            $table->timestamp('teacher_attendance_at')->nullable();
            $table->string('lesson_photo')->nullable();
            $table->text('lesson_description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(
                ['learning_class_id', 'lesson_date', 'lesson_time'],
                'learning_class_lessons_unique_occurrence',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_class_lessons');
    }
};
