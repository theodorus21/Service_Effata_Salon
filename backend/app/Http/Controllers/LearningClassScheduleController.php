<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $role = $request->user()->role;
        $filterRules = [
            'month' => ['nullable', 'date_format:Y-m'],
            'day' => ['nullable', Rule::in(array_keys(self::DAYS))],
        ];
        if (! in_array($role, ['guru', 'murid'], true)) {
            $filterRules['teacher_id'] = ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'guru')];
            $filterRules['student_id'] = ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'murid')];
        } elseif ($role === 'guru') {
            $filterRules['student_id'] = ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'murid')];
        }
        $filters = $request->validate($filterRules);
        $month = $this->selectedMonth($filters['month'] ?? null);
        $teacherId = $role === 'guru'
            ? $request->user()->id
            : ($filters['teacher_id'] ?? null);
        $studentId = $role === 'murid'
            ? $request->user()->id
            : ($filters['student_id'] ?? null);

        $classes = LearningClass::query()
            ->with(['student', 'teacher', 'program', 'schedules'])
            ->whereHas('schedules', function ($query) use ($filters): void {
                if (isset($filters['day'])) {
                    $query->where('day', $filters['day']);
                }
            })
            ->when($teacherId !== null, function ($query) use ($month, $teacherId): void {
                $monthStart = $month->toDateString();
                $monthEnd = $month->endOfMonth()->toDateString();

                $query->where(function ($query) use ($monthStart, $monthEnd, $teacherId): void {
                    $query->whereHas('lessons', function ($lessonQuery) use ($monthStart, $monthEnd, $teacherId): void {
                        $lessonQuery->whereBetween('lesson_date', [$monthStart, $monthEnd])
                            ->where(function ($lessonQuery) use ($teacherId): void {
                                $lessonQuery->where('teacher_id', $teacherId)
                                    ->orWhere(function ($lessonQuery) use ($teacherId): void {
                                        $lessonQuery->whereNull('teacher_id')
                                            ->whereHas('learningClass', fn ($classQuery) => $classQuery->where('teacher_id', $teacherId));
                                    });
                            });
                    })->orWhere(function ($classQuery) use ($monthStart, $monthEnd, $teacherId): void {
                        $classQuery->where('teacher_id', $teacherId)
                            ->whereDoesntHave('lessons', fn ($lessonQuery) => $lessonQuery->whereBetween('lesson_date', [$monthStart, $monthEnd]));
                    });
                });
            })
            ->when($studentId !== null, fn ($query) => $query->where('student_id', $studentId))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('class-schedules.index', [
            'classes' => $classes,
            'teachers' => $role === 'guru' || $role === 'murid'
                ? collect()
                : User::query()->where('role', 'guru')->orderBy('name')->get(),
            'students' => $role === 'guru'
                ? $this->studentsTaughtBy($request->user())
                : ($role === 'murid'
                    ? collect()
                    : User::query()->where('role', 'murid')->orderBy('name')->get()),
            'showTeacherFilter' => ! in_array($role, ['guru', 'murid'], true),
            'showStudentFilter' => $role !== 'murid',
            'days' => self::DAYS,
            'month' => $month,
        ]);
    }

    private function studentsTaughtBy(User $teacher)
    {
        $primaryClassStudentIds = LearningClass::query()
            ->select('student_id')
            ->where('teacher_id', $teacher->id);
        $substituteClassStudentIds = DB::table('learning_class_lessons')
            ->join('learning_classes', 'learning_classes.id', '=', 'learning_class_lessons.learning_class_id')
            ->select('learning_classes.student_id')
            ->where('learning_class_lessons.teacher_id', $teacher->id);

        return User::query()
            ->where('role', 'murid')
            ->where(function (Builder $query) use ($primaryClassStudentIds, $substituteClassStudentIds): void {
                $query->whereIn('id', $primaryClassStudentIds)
                    ->orWhereIn('id', $substituteClassStudentIds);
            })
            ->orderBy('name')
            ->get();
    }

    public function show(Request $request, LearningClass $learningClass): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $user = $request->user();
        if ($user->role === 'murid') {
            abort_unless($learningClass->student_id === $user->id, 404);
        } elseif ($user->role === 'guru') {
            abort_unless(
                $learningClass->lessons()
                    ->where(function ($query) use ($user, $learningClass): void {
                        $query->where('teacher_id', $user->id)
                            ->orWhere(function ($query) use ($user, $learningClass): void {
                                $query->whereNull('teacher_id')
                                    ->whereHas('learningClass', fn ($classQuery) => $classQuery
                                        ->where('teacher_id', $user->id)
                                        ->where('learning_classes.id', $learningClass->id));
                            });
                    })
                    ->exists()
                    || (! $learningClass->lessons()->exists() && $learningClass->teacher_id === $user->id),
                404,
            );
        }

        $learningClass->load(['student', 'teacher', 'program', 'category', 'schedules']);
        $lessons = $learningClass->lessons()
            ->with(['lessonStatus', 'teacher'])
            ->whereYear('lesson_date', $year)
            ->whereMonth('lesson_date', $month)
            ->when($user->role === 'murid', fn ($query) => $query->where('learning_class_id', $learningClass->id))
            ->when($user->role === 'guru', function ($query) use ($user, $learningClass): void {
                $query->where(function ($query) use ($user, $learningClass): void {
                    $query->where('teacher_id', $user->id)
                        ->orWhere(function ($query) use ($user, $learningClass): void {
                            $query->whereNull('teacher_id')
                                ->whereHas('learningClass', fn ($classQuery) => $classQuery
                                    ->where('teacher_id', $user->id)
                                    ->where('learning_classes.id', $learningClass->id));
                        });
                });
            })
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
