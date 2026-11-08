<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_class_lesson_students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_class_lesson_id')
                ->constrained('learning_class_lessons')
                ->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->string('attendance_status', 30)->nullable();
            $table->timestamp('attendance_at')->nullable();
            $table->text('lesson_description')->nullable();
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->string('lesson_photo')->nullable();
            $table->timestamps();
            $table->unique(['learning_class_lesson_id', 'student_id'], 'lcls_lesson_student_unique');
            $table->index('student_id', 'lcls_student_idx');
        });

        DB::table('learning_class_lessons')
            ->select([
                'id',
                'learning_class_id',
                'lesson_description',
                'notes',
                'reason',
                'lesson_photo',
                'created_at',
                'updated_at',
            ])
            ->orderBy('learning_class_lessons.id')
            ->chunkById(500, function ($lessons): void {
                $studentsByClass = DB::table('learning_class_students')
                    ->whereIn('learning_class_id', $lessons->pluck('learning_class_id'))
                    ->get(['learning_class_id', 'student_id'])
                    ->groupBy('learning_class_id');

                $records = $lessons->flatMap(function (object $lesson) use ($studentsByClass): array {
                    return $studentsByClass->get($lesson->learning_class_id, collect())
                        ->map(fn (object $student): array => [
                            'learning_class_lesson_id' => $lesson->id,
                            'student_id' => $student->student_id,
                            'lesson_description' => $lesson->lesson_description,
                            'notes' => $lesson->notes,
                            'reason' => $lesson->reason,
                            'lesson_photo' => $lesson->lesson_photo,
                            'created_at' => $lesson->created_at,
                            'updated_at' => $lesson->updated_at,
                        ])
                        ->all();
                })->all();

                if ($records !== []) {
                    DB::table('learning_class_lesson_students')->insertOrIgnore($records);
                }
            });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->dropColumn(['lesson_description', 'notes', 'reason', 'lesson_photo']);
        });
    }

    public function down(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->text('lesson_description')->nullable();
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->string('lesson_photo')->nullable();
        });

        DB::table('learning_class_lessons')
            ->orderBy('id')
            ->chunkById(500, function ($lessons): void {
                $records = DB::table('learning_class_lesson_students')
                    ->whereIn('learning_class_lesson_id', $lessons->pluck('id'))
                    ->orderBy('id')
                    ->get()
                    ->groupBy('learning_class_lesson_id');

                foreach ($lessons as $lesson) {
                    $record = $records->get($lesson->id)?->first();

                    if ($record === null) {
                        continue;
                    }

                    DB::table('learning_class_lessons')
                        ->where('id', $lesson->id)
                        ->update([
                            'lesson_description' => $record->lesson_description,
                            'notes' => $record->notes,
                            'reason' => $record->reason,
                            'lesson_photo' => $record->lesson_photo,
                        ]);
                }
            });

        Schema::dropIfExists('learning_class_lesson_students');
    }
};
