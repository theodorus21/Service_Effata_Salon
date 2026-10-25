<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LearningClassSchedule;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $classes = LearningClass::query()
            ->with(['student', 'teacher', 'program', 'category', 'schedules', 'product.frequency'])
            ->when($request->user()->role === 'murid', fn ($query) => $query->where('student_id', $request->user()->id))
            ->when($request->user()->role === 'guru', fn ($query) => $query->where('teacher_id', $request->user()->id));

        return view('learning-classes.index', [
            'classes' => $classes->orderBy('name')->paginate(10)->withQueryString(),
            'days' => self::DAYS,
        ]);
    }

    public function create(): View
    {
        return $this->form(new LearningClass);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $product = $data['product'];

        DB::transaction(function () use ($data, $product): void {
            $learningClass = LearningClass::create([
                'name' => $product->name,
                'product_id' => $product->id,
                'price' => $product->price,
                'student_id' => $data['student_id'],
                'teacher_id' => $data['teacher_id'],
                'status' => 'active',
            ]);
            $this->saveSessions($learningClass, $data);
        });

        return redirect()
            ->route('learning-classes.index')
            ->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function show(Request $request, LearningClass $learningClass): View
    {
        $user = $request->user();
        if ($user->role === 'murid') {
            abort_unless($learningClass->student_id === $user->id, 404);
        } elseif ($user->role === 'guru') {
            abort_unless($learningClass->teacher_id === $user->id, 404);
        }

        $learningClass->load(['student', 'teacher', 'program', 'category', 'schedules', 'lessons', 'product.frequency']);

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
        $product = $data['product'];

        DB::transaction(function () use ($data, $product, $learningClass): void {
            $productChanged = $product->id !== (int) $learningClass->product_id;

            $learningClass->update([
                ...($productChanged ? ['name' => $product->name, 'product_id' => $product->id, 'price' => $product->price] : []),
                'student_id' => $data['student_id'],
                'teacher_id' => $data['teacher_id'],
                'status' => $data['status'],
            ]);

            if ($data['is_monthly']) {
                $learningClass->schedules()->delete();
            } else {
                $learningClass->lessons()->whereKey($data['lesson_ids_to_delete'])->delete();
            }

            $this->saveSessions($learningClass, $data);
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
        $learningClass->load(['schedules', 'lessons']);
        $scheduleRows = old('schedules', $learningClass->schedules
            ->map(fn ($schedule): array => [
                'day' => $schedule->day,
                'lesson_time' => substr($schedule->lesson_time, 0, 5),
            ])
            ->all());

        if ($scheduleRows === []) {
            $scheduleRows = [['day' => '', 'lesson_time' => '']];
        }

        $lessonRows = old('lessons', $learningClass->lessons
            ->map(fn ($lesson): array => [
                'lesson_date' => Carbon::parse($lesson->lesson_date)->toDateString(),
                'lesson_time' => substr($lesson->lesson_time, 0, 5),
            ])
            ->values()
            ->all());

        if ($lessonRows === []) {
            $lessonRows = [['lesson_date' => '', 'lesson_time' => '']];
        }

        return view('learning-classes.form', [
            'learningClass' => $learningClass,
            'scheduleRows' => $scheduleRows,
            'lessonRows' => $lessonRows,
            'students' => User::query()->where('role', 'murid')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'guru')->orderBy('name')->get(),
            'products' => Product::query()
                ->with(['program', 'category', 'frequency'])
                ->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->when($learningClass->product_id, fn ($query, $id) => $query->orWhere('id', $id)))
                ->orderBy('name')
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
            ...($learningClass ? ['status' => ['required', Rule::in(LearningClass::STATUSES)]] : []),
            'product_id' => ['required', Rule::exists('products', 'id')->where(fn ($query) => $query->where(fn ($query) => $query
                ->where('status', 'active')
                ->when($learningClass, fn ($query) => $query->orWhere('id', $learningClass->product_id))))],
            'student_id' => ['required', Rule::exists('users', 'id')->where('role', 'murid')],
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'guru')],
        ]);

        $product = Product::with('frequency')->findOrFail($data['product_id']);
        $isMonthly = (bool) $product->frequency?->is_monthly;
        $duration = (int) $product->duration_minutes;
        $errors = [];
        $slots = [];
        $schedules = [];
        $lessonsToCreate = [];
        $lessonIdsToDelete = [];

        if ($learningClass && $learningClass->isMonthly() !== $isMonthly) {
            $errors['product_id'][] = 'Frekuensi bulanan dan non-bulanan tidak dapat saling diganti pada kelas yang sudah ada.';
        }

        if ($isMonthly) {
            $validated = $request->validate([
                'schedules' => ['required', 'array', 'min:1', 'max:20'],
                'schedules.*.day' => ['required', Rule::in(array_keys(self::DAYS))],
                'schedules.*.lesson_time' => ['required', 'date_format:H:i'],
            ]);

            foreach ($validated['schedules'] as $index => $schedule) {
                $slots[] = $this->slot($schedule['day'], null, $schedule['lesson_time'], $duration, "schedules.$index.lesson_time");
                $schedules[] = [
                    'day' => $schedule['day'],
                    'lesson_time' => $schedule['lesson_time'],
                    'duration_minutes' => $duration,
                ];
            }
        } else {
            $validated = $request->validate([
                'lessons' => ['required', 'array', 'min:1', 'max:50'],
                'lessons.*.lesson_date' => ['required', 'date_format:Y-m-d'],
                'lessons.*.lesson_time' => ['required', 'date_format:H:i'],
            ]);

            $existingLessons = $learningClass ? $learningClass->lessons()->get() : collect();
            $existingKeys = $existingLessons
                ->map(fn (LearningClassLesson $lesson): string => $this->lessonKey($lesson->lesson_date, $lesson->lesson_time))
                ->all();
            $submittedKeys = [];
            $today = now()->toDateString();

            foreach ($validated['lessons'] as $index => $lesson) {
                $key = $this->lessonKey($lesson['lesson_date'], $lesson['lesson_time']);
                $isExisting = in_array($key, $existingKeys, true);

                if (! $isExisting && $lesson['lesson_date'] < $today) {
                    $errors["lessons.$index.lesson_date"][] = 'Tanggal pertemuan sudah lewat.';
                }

                if (! $isExisting) {
                    $lessonsToCreate[] = [
                        'teacher_id' => $data['teacher_id'],
                        'lesson_date' => $lesson['lesson_date'],
                        'lesson_time' => $lesson['lesson_time'],
                        'duration_minutes' => $duration,
                    ];
                }

                $submittedKeys[] = $key;
                $slots[] = $this->slot(null, $lesson['lesson_date'], $lesson['lesson_time'], $duration, "lessons.$index.lesson_time");
            }

            foreach ($existingLessons as $lesson) {
                if (in_array($this->lessonKey($lesson->lesson_date, $lesson->lesson_time), $submittedKeys, true)) {
                    continue;
                }

                if ($this->lessonHasProgress($lesson)) {
                    $errors['lessons'][] = sprintf(
                        'Pertemuan %s pukul %s sudah memiliki status atau catatan dan tidak dapat dihapus.',
                        Carbon::parse($lesson->lesson_date)->format('d/m/Y'),
                        substr($lesson->lesson_time, 0, 5),
                    );
                }

                $lessonIdsToDelete[] = $lesson->getKey();
            }
        }

        foreach ($slots as $index => $slot) {
            foreach (array_slice($slots, $index + 1, null, true) as $otherIndex => $otherSlot) {
                if ($this->slotsConflict($slot, $otherSlot)) {
                    $errors[$otherSlot['field']][] = 'Jadwal ini bertabrakan dengan jadwal lain pada kelas ini.';
                }
            }

            foreach ($this->existingSlots($data, $learningClass) as $existingSlot) {
                if ($this->slotsConflict($slot, $existingSlot)) {
                    $errors[$slot['field']][] = sprintf(
                        'Jadwal bertabrakan dengan kelas "%s" pada %s pukul %s.',
                        $existingSlot['class_name'],
                        $existingSlot['date'] !== null
                            ? Carbon::parse($existingSlot['date'])->format('d/m/Y')
                            : self::DAYS[$existingSlot['day']],
                        $slot['time'],
                    );
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [
            ...$data,
            'product' => $product,
            'is_monthly' => $isMonthly,
            'schedules' => $schedules,
            'lessons_to_create' => $lessonsToCreate,
            'lesson_ids_to_delete' => $lessonIdsToDelete,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSessions(LearningClass $learningClass, array $data): void
    {
        if ($data['is_monthly']) {
            $learningClass->schedules()->createMany($data['schedules']);

            return;
        }

        $learningClass->lessons()->createMany($data['lessons_to_create']);
    }

    /**
     * Jadwal mingguan memakai hari saja; jadwal tanggal memakai tanggal dan hari dari tanggal tersebut.
     *
     * @return array{day: ?string, date: ?string, start: int, end: int, time: string, field: string, class_name: string}
     */
    private function slot(?string $day, ?string $date, string $time, int $duration, string $field = '', string $className = ''): array
    {
        $start = $this->minutesAfterMidnight($time);

        return [
            'day' => $date !== null ? $this->dayKeyFor($date) : $day,
            'date' => $date,
            'start' => $start,
            'end' => $start + $duration,
            'time' => substr($time, 0, 5),
            'field' => $field,
            'class_name' => $className,
        ];
    }

    /**
     * @param  array{day: ?string, date: ?string, start: int, end: int}  $slot
     * @param  array{day: ?string, date: ?string, start: int, end: int}  $otherSlot
     */
    private function slotsConflict(array $slot, array $otherSlot): bool
    {
        if (! $this->intervalsOverlap($slot['start'], $slot['end'], $otherSlot['start'], $otherSlot['end'])) {
            return false;
        }

        if ($slot['date'] !== null && $otherSlot['date'] !== null) {
            return $slot['date'] === $otherSlot['date'];
        }

        return $slot['day'] !== null && $slot['day'] === $otherSlot['day'];
    }

    /**
     * Jadwal dan pertemuan kelas lain yang memakai murid atau guru yang sama.
     *
     * @param  array<string, mixed>  $data
     * @return list<array{day: ?string, date: ?string, start: int, end: int, time: string, field: string, class_name: string}>
     */
    private function existingSlots(array $data, ?LearningClass $learningClass): array
    {
        $classes = LearningClass::query()
            ->where(fn ($query) => $query
                ->where('student_id', $data['student_id'])
                ->orWhere('teacher_id', $data['teacher_id']))
            ->when($learningClass, fn ($query) => $query->whereKeyNot($learningClass->getKey()))
            ->get(['id', 'name']);

        $classIds = $classes->modelKeys();
        $classNames = $classes->pluck('name', 'id');
        $slots = [];

        foreach (LearningClassSchedule::query()->whereIn('learning_class_id', $classIds)->get() as $schedule) {
            $slots[] = $this->slot(
                $schedule->day,
                null,
                $schedule->lesson_time,
                (int) $schedule->duration_minutes,
                className: $classNames[$schedule->learning_class_id],
            );
        }

        $lessons = LearningClassLesson::query()
            ->whereIn('learning_class_id', $classIds)
            ->where('lesson_date', '>=', now()->toDateString())
            ->with('lessonStatus')
            ->get()
            ->reject(fn (LearningClassLesson $lesson): bool => $lesson->lessonStatus?->system_key === 'dibatalkan');

        foreach ($lessons as $lesson) {
            $slots[] = $this->slot(
                null,
                Carbon::parse($lesson->lesson_date)->toDateString(),
                $lesson->lesson_time,
                (int) $lesson->duration_minutes,
                className: $classNames[$lesson->learning_class_id],
            );
        }

        return $slots;
    }

    private function dayKeyFor(string $date): ?string
    {
        $isoDay = Carbon::parse($date)->dayOfWeekIso;

        return $isoDay <= 6 ? array_keys(self::DAYS)[$isoDay - 1] : null;
    }

    private function lessonKey(mixed $date, string $time): string
    {
        return Carbon::parse($date)->toDateString().' '.substr($time, 0, 5);
    }

    private function lessonHasProgress(LearningClassLesson $lesson): bool
    {
        return (int) $lesson->status !== 0
            || filled($lesson->teacher_attendance_at)
            || filled($lesson->reason)
            || filled($lesson->notes)
            || filled($lesson->lesson_photo);
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
