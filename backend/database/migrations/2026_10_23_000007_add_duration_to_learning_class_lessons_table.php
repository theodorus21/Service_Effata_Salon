<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DAYS = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
    ];

    public function up(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('lesson_time');
        });

        DB::table('learning_class_lessons')
            ->select(['id', 'learning_class_id', 'lesson_date', 'lesson_time'])
            ->orderBy('id')
            ->each(function (object $lesson): void {
                $day = self::DAYS[CarbonImmutable::parse($lesson->lesson_date)->isoWeekday()] ?? null;

                if ($day === null) {
                    return;
                }

                $duration = DB::table('learning_class_schedules')
                    ->where('learning_class_id', $lesson->learning_class_id)
                    ->where('day', $day)
                    ->where('lesson_time', $lesson->lesson_time)
                    ->value('duration_minutes');

                if ($duration !== null) {
                    DB::table('learning_class_lessons')
                        ->where('id', $lesson->id)
                        ->update(['duration_minutes' => $duration]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->dropColumn('duration_minutes');
        });
    }
};
