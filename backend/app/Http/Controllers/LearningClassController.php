<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\LearningClassSchedule;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LearningClassController extends Controller
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
        ]);

        return view('learning-classes.index', [
            'classes' => LearningClass::query()
                ->with(['student', 'teacher', 'program', 'category', 'schedules'])
                ->orderBy('name')
                ->paginate(10),
            'days' => self::DAYS,
            'month' => $filters['month'] ?? now()->format('Y-m'),
        ]);
    }

    public function create(): View
    {
        return $this->form(new LearningClass);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data): void {
            $learningClass = LearningClass::create([
                'name' => $data['name'],
                'student_id' => $data['student_id'],
                'teacher_id' => $data['teacher_id'],
                'program_id' => $data['program_id'],
                'category_id' => $data['category_id'],
            ]);
            $learningClass->schedules()->createMany($data['schedules']);
        });

        return redirect()
            ->route('learning-classes.index')
            ->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function show(LearningClass $learningClass): View
    {
        $learningClass->load(['student', 'teacher', 'program', 'category', 'schedules']);

        return view('learning-classes.show', [
            'learningClass' => $learningClass,
            'days' => self::DAYS,
        ]);
    }

    public function edit(LearningClass $learningClass): View
    {
        return $this->form($learningClass);
    }

    public function update(Request $request, LearningClass $learningClass): RedirectResponse
    {
        $data = $this->validatedData($request, $learningClass);

        DB::transaction(function () use ($data, $learningClass): void {
            $learningClass->update([
                'name' => $data['name'],
                'student_id' => $data['student_id'],
                'teacher_id' => $data['teacher_id'],
                'program_id' => $data['program_id'],
                'category_id' => $data['category_id'],
            ]);
            $learningClass->schedules()->delete();
            $learningClass->schedules()->createMany($data['schedules']);
        });

        return redirect()
            ->route('learning-classes.index')
            ->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(LearningClass $learningClass): RedirectResponse
    {
        $learningClass->delete();

        return redirect()
            ->route('learning-classes.index')
            ->with('status', 'Kelas berhasil dihapus.');
    }

    private function form(LearningClass $learningClass): View
    {
        $learningClass->load('schedules');
        $scheduleRows = old('schedules', $learningClass->schedules
            ->map(fn ($schedule): array => [
                'day' => $schedule->day,
                'lesson_time' => substr($schedule->lesson_time, 0, 5),
                'duration_minutes' => $schedule->duration_minutes,
            ])
            ->all());

        if ($scheduleRows === []) {
            $scheduleRows = [['day' => '', 'lesson_time' => '', 'duration_minutes' => '']];
        }

        return view('learning-classes.form', [
            'learningClass' => $learningClass,
            'scheduleRows' => $scheduleRows,
            'students' => User::query()->where('role', 'murid')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'guru')->orderBy('name')->get(),
            'programs' => LookupValue::query()
                ->where('category', 'program')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->get(),
            'categories' => LookupValue::query()
                ->where('category', 'class_category')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->get(),
            'days' => self::DAYS,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?LearningClass $learningClass = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', Rule::exists('users', 'id')->where('role', 'murid')],
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'guru')],
            'program_id' => ['required', Rule::exists('lookup_values', 'id')->where('category', 'program')],
            'category_id' => ['required', Rule::exists('lookup_values', 'id')->where('category', 'class_category')],
            'schedules' => ['required', 'array', 'min:1', 'max:20'],
            'schedules.*.day' => ['required', Rule::in(array_keys(self::DAYS))],
            'schedules.*.lesson_time' => ['required', 'date_format:H:i'],
            'schedules.*.duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        $errors = [];
        $submittedSchedules = $data['schedules'];

        foreach ($submittedSchedules as $index => $schedule) {
            $start = $this->minutesAfterMidnight($schedule['lesson_time']);
            $end = $start + (int) $schedule['duration_minutes'];

            foreach (array_slice($submittedSchedules, $index + 1, null, true) as $otherIndex => $otherSchedule) {
                if (
                    $schedule['day'] === $otherSchedule['day']
                    && $this->intervalsOverlap(
                        $start,
                        $end,
                        $this->minutesAfterMidnight($otherSchedule['lesson_time']),
                        $this->minutesAfterMidnight($otherSchedule['lesson_time']) + (int) $otherSchedule['duration_minutes'],
                    )
                ) {
                    $errors["schedules.$otherIndex.lesson_time"][] =
                        'Jadwal ini bertabrakan dengan jadwal lain pada hari yang sama.';
                }
            }
        }

        $conflictingSchedules = LearningClassSchedule::query()
            ->with('learningClass')
            ->whereIn('day', array_column($submittedSchedules, 'day'))
            ->whereHas('learningClass', function ($query) use ($data, $learningClass): void {
                $query->where(function ($query) use ($data): void {
                    $query->where('student_id', $data['student_id'])
                        ->orWhere('teacher_id', $data['teacher_id']);
                });

                if ($learningClass) {
                    $query->where('learning_classes.id', '!=', $learningClass->getKey());
                }
            })
            ->get();

        foreach ($submittedSchedules as $index => $schedule) {
            $start = $this->minutesAfterMidnight($schedule['lesson_time']);
            $end = $start + (int) $schedule['duration_minutes'];

            foreach ($conflictingSchedules as $existingSchedule) {
                if (
                    $schedule['day'] === $existingSchedule->day
                    && $this->intervalsOverlap(
                        $start,
                        $end,
                        $this->minutesAfterMidnight($existingSchedule->lesson_time),
                        $this->minutesAfterMidnight($existingSchedule->lesson_time) + $existingSchedule->duration_minutes,
                    )
                ) {
                    $errors["schedules.$index.lesson_time"][] = sprintf(
                        'Jadwal bertabrakan dengan kelas "%s" pada %s pukul %s.',
                        $existingSchedule->learningClass->name,
                        self::DAYS[$existingSchedule->day],
                        substr($existingSchedule->lesson_time, 0, 5),
                    );
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    private function minutesAfterMidnight(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hours * 60) + $minutes;
    }

    private function intervalsOverlap(int $start, int $end, int $otherStart, int $otherEnd): bool
    {
        return $start < $otherEnd && $otherStart < $end;
    }
}
