<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function create(LearningClass $learningClass): View
    {
        $learningClass->load(['schedules', 'student', 'teacher']);

        return view('learning-classes.generate-lessons', [
            'learningClass' => $learningClass,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    public function generate(Request $request, LearningClass $learningClass): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);
        $month = CarbonImmutable::createFromDate((int) $data['year'], (int) $data['month'], 1);
        $schedulesByDay = $learningClass->schedules->groupBy('day');
        $lessons = [];
        $now = now();
        $today = CarbonImmutable::today();

        for ($date = $month->startOfMonth(); $date <= $month->endOfMonth(); $date = $date->addDay()) {
            if (! $date->isAfter($today)) {
                continue;
            }

            foreach ($schedulesByDay->get(self::ISO_DAYS[$date->isoWeekday()], collect()) as $schedule) {
                $lessons[] = [
                    'learning_class_id' => $learningClass->id,
                    'teacher_id' => $learningClass->teacher_id,
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
