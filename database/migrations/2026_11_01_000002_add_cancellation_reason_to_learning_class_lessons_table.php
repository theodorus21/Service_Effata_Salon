<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->text('reason')->nullable();
        });

        DB::table('learning_class_lessons')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($lessons): void {
                $reasonsByLesson = DB::table('learning_class_lesson_students')
                    ->whereIn('learning_class_lesson_id', $lessons->pluck('id'))
                    ->whereNotNull('reason')
                    ->orderBy('id')
                    ->get(['learning_class_lesson_id', 'reason'])
                    ->groupBy('learning_class_lesson_id');

                foreach ($lessons as $lesson) {
                    $reason = $reasonsByLesson->get($lesson->id)?->first()?->reason;

                    if ($reason !== null) {
                        DB::table('learning_class_lessons')
                            ->where('id', $lesson->id)
                            ->update(['reason' => $reason]);
                    }
                }
            });

        DB::table('learning_class_lesson_students')
            ->whereNotNull('reason')
            ->update(['reason' => null]);

        DB::table('learning_class_lesson_students')
            ->whereNotNull('attendance_status')
            ->where('attendance_status', '!=', 'present')
            ->update([
                'attendance_status' => null,
                'attendance_at' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('learning_class_lessons')
            ->whereNotNull('reason')
            ->orderBy('id')
            ->chunkById(500, function ($lessons): void {
                foreach ($lessons as $lesson) {
                    DB::table('learning_class_lesson_students')
                        ->where('learning_class_lesson_id', $lesson->id)
                        ->whereNull('reason')
                        ->update(['reason' => $lesson->reason]);
                }
            });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->dropColumn('reason');
        });
    }
};
