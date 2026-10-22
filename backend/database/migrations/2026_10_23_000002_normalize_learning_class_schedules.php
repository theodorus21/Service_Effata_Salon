<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_class_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_class_id')
                ->constrained('learning_classes')
                ->cascadeOnDelete();
            $table->string('day', 20);
            $table->time('lesson_time');
            $table->unsignedSmallInteger('duration_minutes');
            $table->timestamps();
            $table->index(['learning_class_id', 'day']);
        });

        DB::table('learning_classes')
            ->select(['id', 'day', 'lesson_time', 'duration_minutes', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->each(function (object $learningClass): void {
                DB::table('learning_class_schedules')->insert([
                    'learning_class_id' => $learningClass->id,
                    'day' => $learningClass->day,
                    'lesson_time' => $learningClass->lesson_time,
                    'duration_minutes' => $learningClass->duration_minutes,
                    'created_at' => $learningClass->created_at,
                    'updated_at' => $learningClass->updated_at,
                ]);
            });

        Schema::table('learning_classes', function (Blueprint $table): void {
            $table->dropColumn(['day', 'lesson_time', 'duration_minutes']);
        });
    }

    public function down(): void
    {
        Schema::table('learning_classes', function (Blueprint $table): void {
            $table->string('day', 20)->nullable();
            $table->time('lesson_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
        });

        DB::table('learning_class_schedules')
            ->orderBy('id')
            ->get()
            ->each(function (object $schedule): void {
                $alreadyCopied = DB::table('learning_classes')
                    ->where('id', $schedule->learning_class_id)
                    ->whereNotNull('day')
                    ->exists();

                if (! $alreadyCopied) {
                    DB::table('learning_classes')
                        ->where('id', $schedule->learning_class_id)
                        ->update([
                            'day' => $schedule->day,
                            'lesson_time' => $schedule->lesson_time,
                            'duration_minutes' => $schedule->duration_minutes,
                        ]);
                }
            });

        Schema::dropIfExists('learning_class_schedules');
    }
};
