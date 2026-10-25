<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table) {
            $table->index(
                ['learning_class_id', 'lesson_date', 'lesson_time'],
                'learning_class_lessons_occurrence_index',
            );
            $table->dropUnique('learning_class_lessons_unique_occurrence');
        });
    }

    public function down(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table) {
            $table->unique(
                ['learning_class_id', 'lesson_date', 'lesson_time'],
                'learning_class_lessons_unique_occurrence',
            );
            $table->dropIndex('learning_class_lessons_occurrence_index');
        });
    }
};
