<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningClassLessonController extends Controller
{
    private const ISO_DAYS = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu',
    ];

    public function generate(Request $request, LearningClass $learningClass): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month']);
        $schedulesByDay = $learningClass->schedules->groupBy('day');
        $lessons = [];
        $now = now();

        for ($date = $month->startOfMonth(); $date <= $month->endOfMonth(); $date = $date->addDay()) {
            foreach ($schedulesByDay->get(self::ISO_DAYS[$date->isoWeekday()], collect()) as $schedule) {
                $lessons[] = [
                    'learning_class_id' => $learningClass->id,
                    'lesson_date' => $date->toDateString(),
                    'lesson_time' => $schedule->lesson_time,
                    'duration_minutes' => $schedule->duration_minutes,
                    'status' => 0,
                    'teacher_attendance_at' => null,
                    'lesson_photo' => null,
                    'lesson_description' => null,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $createdCount = $lessons === []
            ? 0
            : DB::table('learning_class_lessons')->insertOrIgnore($lessons);

        return redirect()
            ->route('learning-classes.index', ['month' => $month->format('Y-m')])
            ->with('status', sprintf(
                '%d jadwal les berhasil dibuat untuk %s (%s). Jadwal yang sudah ada tidak diubah.',
                $createdCount,
                $learningClass->name,
                $month->locale('id')->translatedFormat('F Y'),
            ));
    }
}
