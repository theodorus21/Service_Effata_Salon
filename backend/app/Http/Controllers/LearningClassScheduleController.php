<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LearningClassScheduleController extends Controller
{
    private const DAYS = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'guru')],
            'student_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'murid')],
            'day' => ['nullable', Rule::in(array_keys(self::DAYS))],
        ]);
        $month = $this->selectedMonth($filters['month'] ?? null);

        $classes = LearningClass::query()
            ->with(['student', 'teacher', 'program', 'schedules'])
            ->whereHas('schedules', function ($query) use ($filters): void {
                if (isset($filters['day'])) {
                    $query->where('day', $filters['day']);
                }
            })
            ->when(isset($filters['teacher_id']), fn ($query) => $query->where('teacher_id', $filters['teacher_id']))
            ->when(isset($filters['student_id']), fn ($query) => $query->where('student_id', $filters['student_id']))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('class-schedules.index', [
            'classes' => $classes,
            'teachers' => User::query()->where('role', 'guru')->orderBy('name')->get(),
            'students' => User::query()->where('role', 'murid')->orderBy('name')->get(),
            'days' => self::DAYS,
            'month' => $month,
        ]);
    }

    public function show(Request $request, LearningClass $learningClass): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $learningClass->load(['student', 'teacher', 'program', 'category', 'schedules']);
        $lessons = $learningClass->lessons()
            ->with('lessonStatus')
            ->whereYear('lesson_date', $year)
            ->whereMonth('lesson_date', $month)
            ->paginate(15)
            ->withQueryString();

        return view('class-schedules.show', [
            'learningClass' => $learningClass,
            'lessons' => $lessons,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function selectedMonth(?string $month): CarbonImmutable
    {
        return $month
            ? CarbonImmutable::createFromFormat('!Y-m', $month)
            : CarbonImmutable::now()->startOfMonth();
    }
}
