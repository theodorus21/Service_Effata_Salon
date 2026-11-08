<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LearningClassLessonRescheduleHistory;
use App\Models\LearningClassLessonStatus;
use App\Models\LearningClassLessonStudent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearningClassLessonActionController extends Controller
{
    public function show(LearningClass $learningClass, LearningClassLesson $lesson): View
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsVisibleToUser($learningClass, $lesson);
        $lesson->load([
            'lessonStatus',
            'teacher',
            'rescheduleHistories.updatedBy',
            'rescheduleHistories.oldTeacher',
            'rescheduleHistories.newTeacher',
        ]);
        $learningClass->load(['students', 'teacher', 'program', 'category']);
        $studentRecords = $lesson->studentRecords()
            ->with('student')
            ->when(
                request()->user()->role === 'murid',
                fn ($query) => $query->where('student_id', request()->user()->id),
            )
            ->get();
        $lesson->setRelation('studentRecords', $studentRecords);

        return view('class-schedules.lesson-detail', compact('learningClass', 'lesson'));
    }

    public function attendance(LearningClass $learningClass, LearningClassLesson $lesson): RedirectResponse
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        abort_unless($lesson->lessonStatus?->system_key === 'belum_terlaksana', 422, 'Absensi hanya tersedia untuk jadwal yang belum terlaksana.');

        if ($lesson->teacher_attendance_at) {
            return $this->returnToLessonList($lesson)
                ->with('status', 'Absensi guru untuk jadwal ini sudah tercatat.');
        }

        $inProgressStatus = LearningClassLessonStatus::query()
            ->where('system_key', 'sedang_berlangsung')
            ->firstOrFail();

        $lesson->update([
            'teacher_attendance_at' => now(),
            'status' => $inProgressStatus->code,
        ]);

        return $this->returnToLessonList($lesson)
            ->with('status', 'Absensi guru berhasil dicatat. Status jadwal menjadi '.$inProgressStatus->label.'.');
    }

    public function editReport(LearningClass $learningClass, LearningClassLesson $lesson): View
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsVisibleToUser($learningClass, $lesson);
        abort_unless($lesson->lessonStatus?->system_key === 'sedang_berlangsung', 422, 'Laporan hanya dapat dibuat saat kelas sedang berlangsung.');
        $learningClass->load(['students', 'teacher']);
        $studentRecords = $lesson->studentRecords()
            ->with('student')
            ->when(
                request()->user()->role === 'murid',
                fn ($query) => $query->where('student_id', request()->user()->id),
            )
            ->orderBy('student_id')
            ->get();
        $lesson->setRelation('studentRecords', $studentRecords);

        return view('class-schedules.report', compact('learningClass', 'lesson'));
    }

    public function editStudentReport(
        LearningClass $learningClass,
        LearningClassLesson $lesson,
        LearningClassLessonStudent $studentRecord,
    ): View {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsVisibleToUser($learningClass, $lesson);
        $this->ensureStudentRecordBelongsToLesson($lesson, $studentRecord);
        $this->ensureStudentRecordIsVisibleToUser($studentRecord);
        abort_unless($lesson->lessonStatus?->system_key === 'sedang_berlangsung', 422, 'Laporan hanya dapat dibuat saat kelas sedang berlangsung.');

        return view('class-schedules.report-student', [
            'learningClass' => $learningClass->load(['teacher']),
            'lesson' => $lesson,
            'studentRecord' => $studentRecord->load('student'),
            'attendanceStatuses' => LearningClassLessonStudent::ATTENDANCE_STATUSES,
        ]);
    }

    public function updateReport(
        Request $request,
        LearningClass $learningClass,
        LearningClassLesson $lesson,
        LearningClassLessonStudent $studentRecord,
    ): RedirectResponse {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsVisibleToUser($learningClass, $lesson);
        $this->ensureStudentRecordBelongsToLesson($lesson, $studentRecord);
        $this->ensureStudentRecordIsVisibleToUser($studentRecord);
        abort_unless($lesson->lessonStatus?->system_key === 'sedang_berlangsung', 422, 'Laporan hanya dapat disimpan saat kelas sedang berlangsung.');

        $completedStatus = $this->completedStatus();

        DB::transaction(function () use ($request, $learningClass, $lesson, $studentRecord, $completedStatus): void {
            $lockedLesson = LearningClassLesson::query()->lockForUpdate()->findOrFail($lesson->id);
            $lockedLesson->load('lessonStatus');
            abort_unless($lockedLesson->lessonStatus?->system_key === 'sedang_berlangsung', 422, 'Laporan hanya dapat disimpan saat kelas sedang berlangsung.');

            $studentRecord = LearningClassLessonStudent::query()
                ->lockForUpdate()
                ->findOrFail($studentRecord->id);
            $attendanceLocked = $studentRecord->attendance_at !== null;
            $attendanceStatus = $attendanceLocked
                ? $studentRecord->attendance_status
                : $request->input('attendance_status');
            $requiresLessonContent = $attendanceStatus === 'present';
            $data = $request->validate([
                'lesson_photo' => $requiresLessonContent
                    ? [
                        $studentRecord->lesson_photo ? 'nullable' : 'required',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:5120',
                    ]
                    : ['prohibited'],
                'lesson_description' => $requiresLessonContent
                    ? ['required', 'string', 'max:5000']
                    : ['prohibited'],
                'notes' => ['nullable', 'string', 'max:5000'],
                'attendance_status' => $attendanceLocked
                    ? ['prohibited']
                    : ['required', Rule::in(array_keys(LearningClassLessonStudent::ATTENDANCE_STATUSES))],
                'reason' => $attendanceLocked || $attendanceStatus === 'present'
                    ? ['prohibited']
                    : ['required', 'string', 'max:5000'],
            ]);

            $updates = ['notes' => $data['notes'] ?? null];
            if ($requiresLessonContent) {
                $photoPath = $studentRecord->lesson_photo;
                if (isset($data['lesson_photo'])) {
                    $photoPath = $data['lesson_photo']->store(
                        'learning-class-lessons/'.$learningClass->id.'/'.$studentRecord->student_id,
                        'local',
                    );
                }

                if ($photoPath === false) {
                    throw new RuntimeException('Gagal menyimpan foto laporan kelas.');
                }
                if ($photoPath === null) {
                    throw ValidationException::withMessages([
                        'lesson_photo' => 'Foto pelajaran wajib diunggah untuk murid ini.',
                    ]);
                }

                $updates['lesson_photo'] = $photoPath;
                $updates['lesson_description'] = $data['lesson_description'];
            }
            if (! $attendanceLocked) {
                $updates['attendance_status'] = $data['attendance_status'];
                $updates['attendance_at'] = now();
                $updates['reason'] = $data['attendance_status'] === 'present'
                    ? null
                    : $data['reason'];
            }
            $studentRecord->update($updates);

            $hasIncompleteReports = $lockedLesson->studentRecords()
                ->where(fn ($query) => $query
                    ->whereNull('attendance_at')
                    ->orWhere(fn ($query) => $query
                        ->where('attendance_status', 'present')
                        ->where(fn ($query) => $query
                            ->whereNull('lesson_photo')
                            ->orWhereNull('lesson_description'))))
                ->exists();

            if (! $hasIncompleteReports) {
                $lockedLesson->update(['status' => $completedStatus->code]);
            }
        });

        return redirect()
            ->route('class-schedules.lessons.show', ['learningClass' => $learningClass, 'lesson' => $lesson])
            ->with('status', 'Laporan '.$studentRecord->student->name.' berhasil disimpan.');
    }

    public function lessonPhoto(
        LearningClass $learningClass,
        LearningClassLesson $lesson,
        LearningClassLessonStudent $studentRecord,
    ): StreamedResponse {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsVisibleToUser($learningClass, $lesson);
        $this->ensureStudentRecordBelongsToLesson($lesson, $studentRecord);
        $this->ensureStudentRecordIsVisibleToUser($studentRecord);
        abort_unless(
            $studentRecord->lesson_photo && Storage::disk('local')->exists($studentRecord->lesson_photo),
            404,
        );

        return Storage::disk('local')->response($studentRecord->lesson_photo);
    }

    public function editReschedule(LearningClass $learningClass, LearningClassLesson $lesson): View
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsPending($lesson, 'Jadwal hanya dapat dipindahkan sebelum kelas berlangsung.');

        return view('class-schedules.reschedule', [
            'learningClass' => $learningClass,
            'lesson' => $lesson->load('teacher'),
            'teachers' => User::query()->where('role', 'guru')->orderBy('name')->get(),
        ]);
    }

    public function updateReschedule(Request $request, LearningClass $learningClass, LearningClassLesson $lesson): RedirectResponse
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsPending($lesson, 'Jadwal hanya dapat dipindahkan sebelum kelas berlangsung.');

        $data = $request->validate([
            'lesson_date' => ['required', 'date'],
            'lesson_time' => [
                'required',
                'date_format:H:i',
            ],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'guru')],
            'reason' => ['required', 'string', 'max:5000'],
        ]);

        $this->validateNoScheduleConflict($learningClass, $lesson, $data);

        if (
            $lesson->lesson_date === $data['lesson_date']
            && substr($lesson->lesson_time, 0, 5) === $data['lesson_time']
            && $lesson->duration_minutes === (int) $data['duration_minutes']
            && ($lesson->teacher_id ?? $learningClass->teacher_id) === (int) $data['teacher_id']
        ) {
            return $this->returnToLessonList($lesson)
                ->with('status', 'Tanggal dan jam tidak berubah; riwayat jadwal tidak ditambahkan.');
        }

        DB::transaction(function () use ($request, $data, $lesson): void {
            $learningClass = $lesson->learningClass;
            LearningClassLessonRescheduleHistory::query()->create([
                'learning_class_lesson_id' => $lesson->id,
                'old_lesson_date' => $lesson->lesson_date,
                'old_lesson_time' => $lesson->lesson_time,
                'new_lesson_date' => $data['lesson_date'],
                'new_lesson_time' => $data['lesson_time'],
                'reason' => $data['reason'],
                'old_teacher_id' => $lesson->teacher_id ?? $learningClass->teacher_id,
                'new_teacher_id' => $data['teacher_id'],
                'updated_by' => $request->user()?->getAuthIdentifier(),
            ]);

            $lesson->update([
                'lesson_date' => $data['lesson_date'],
                'lesson_time' => $data['lesson_time'],
                'duration_minutes' => $data['duration_minutes'],
                'teacher_id' => $data['teacher_id'],
            ]);
        });

        return $this->returnToLessonList($lesson->refresh())
            ->with('status', 'Jadwal les berhasil dipindahkan. Perubahan tercatat di riwayat.');
    }

    public function cancel(Request $request, LearningClass $learningClass, LearningClassLesson $lesson): RedirectResponse
    {
        $this->ensureLessonBelongsToClass($learningClass, $lesson);
        $this->ensureLessonIsPending($lesson, 'Kelas hanya dapat dibatalkan sebelum kelas berlangsung.');

        abort_if(! $lesson->studentRecords()->exists(), 409, 'Daftar murid untuk jadwal ini belum tersedia.');

        $data = $request->validate([
            'cancel_lesson_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'max:5000'],
        ]);

        $cancelledStatus = LearningClassLessonStatus::query()
            ->where('system_key', 'dibatalkan')
            ->firstOrFail();

        DB::transaction(function () use ($data, $lesson, $cancelledStatus): void {
            $lesson->update([
                'status' => $cancelledStatus->code,
                'reason' => $data['reason'],
            ]);
        });

        return $this->returnToLessonList($lesson)
            ->with('status', 'Jadwal les berhasil dibatalkan.');
    }

    private function ensureLessonBelongsToClass(LearningClass $learningClass, LearningClassLesson $lesson): void
    {
        abort_unless($lesson->learning_class_id === $learningClass->id, 404);
    }

    private function ensureLessonIsVisibleToUser(LearningClass $learningClass, LearningClassLesson $lesson): void
    {
        $user = request()->user();

        if ($user->role === 'murid') {
            abort_unless($learningClass->students()->whereKey($user->id)->exists(), 404);
        } elseif ($user->role === 'guru') {
            abort_unless(
                ($lesson->teacher_id ?? $learningClass->teacher_id) === $user->id,
                404,
            );
        }
    }

    private function ensureStudentRecordBelongsToLesson(
        LearningClassLesson $lesson,
        LearningClassLessonStudent $studentRecord,
    ): void {
        abort_unless($studentRecord->learning_class_lesson_id === $lesson->id, 404);
    }

    private function ensureStudentRecordIsVisibleToUser(LearningClassLessonStudent $studentRecord): void
    {
        $user = request()->user();

        if ($user->role === 'murid') {
            abort_unless($studentRecord->student_id === $user->id, 404);
        }
    }

    private function ensureLessonIsPending(LearningClassLesson $lesson, string $message): void
    {
        abort_unless($lesson->lessonStatus?->system_key === 'belum_terlaksana', 422, $message);
    }

    private function completedStatus(): LearningClassLessonStatus
    {
        $status = LearningClassLessonStatus::query()
            ->where('system_key', 'terlaksana')
            ->first();

        if ($status) {
            return $status;
        }

        $status = LearningClassLessonStatus::query()
            ->where('label', 'Terlaksana')
            ->first();

        if ($status) {
            $status->update([
                'color' => '#198754',
                'system_key' => 'terlaksana',
            ]);

            return $status->refresh();
        }

        $code = (int) LearningClassLessonStatus::query()->max('code') + 1;

        return LearningClassLessonStatus::query()->create([
            'code' => $code,
            'label' => 'Terlaksana',
            'color' => '#198754',
            'sort_order' => $code,
            'system_key' => 'terlaksana',
        ]);
    }

    private function returnToLessonList(LearningClassLesson $lesson): RedirectResponse
    {
        $date = Carbon::parse($lesson->lesson_date);

        return redirect()->route('class-schedules.show', [
            'learningClass' => $lesson->learning_class_id,
            'month' => $date->month,
            'year' => $date->year,
        ]);
    }

    /**
     * @param  array{lesson_date: string, lesson_time: string, duration_minutes: int|string, teacher_id: int|string}  $data
     */
    private function validateNoScheduleConflict(
        LearningClass $learningClass,
        LearningClassLesson $lesson,
        array $data,
    ): void {
        $newStart = $this->timestampInMinutes($data['lesson_date'], $data['lesson_time']);
        $newEnd = $newStart + (int) $data['duration_minutes'];
        $date = Carbon::parse($data['lesson_date']);
        $cancelledStatus = LearningClassLessonStatus::query()
            ->where('system_key', 'dibatalkan')
            ->firstOrFail();

        $sameClassOccurrenceExists = LearningClassLesson::query()
            ->where('learning_class_id', $learningClass->id)
            ->whereDate('lesson_date', $data['lesson_date'])
            ->whereTime('lesson_time', $data['lesson_time'])
            ->where('status', '!=', $cancelledStatus->code)
            ->where('id', '!=', $lesson->id)
            ->exists();

        if ($sameClassOccurrenceExists) {
            throw ValidationException::withMessages([
                'lesson_time' => 'Kelas ini sudah memiliki jadwal pada tanggal dan jam tersebut.',
            ]);
        }

        $conflicts = LearningClassLesson::query()
            ->with('learningClass')
            ->whereBetween('lesson_date', [
                $date->copy()->subDay()->toDateString(),
                $date->copy()->addDay()->toDateString(),
            ])
            ->where('status', '!=', $cancelledStatus->code)
            ->where('id', '!=', $lesson->id)
            ->where(function ($query) use ($learningClass, $data): void {
                $query->whereHas('learningClass', function ($classQuery) use ($learningClass): void {
                    $classQuery->whereHas('students', fn ($students) => $students->whereIn(
                        'users.id',
                        $learningClass->students->modelKeys(),
                    ));
                })->orWhere('teacher_id', $data['teacher_id'])
                    ->orWhere(function ($lessonQuery) use ($data): void {
                        $lessonQuery->whereNull('teacher_id')
                            ->whereHas('learningClass', function ($classQuery) use ($data): void {
                                $classQuery->where('teacher_id', $data['teacher_id']);
                            });
                    });
            })
            ->get();

        foreach ($conflicts as $existingLesson) {
            $existingStart = $this->timestampInMinutes(
                $existingLesson->lesson_date,
                $existingLesson->lesson_time,
            );
            $existingEnd = $existingStart + $existingLesson->duration_minutes;

            if ($newStart < $existingEnd && $existingStart < $newEnd) {
                throw ValidationException::withMessages([
                    'lesson_time' => sprintf(
                        'Jadwal bertabrakan dengan sesi kelas "%s" pada %s pukul %s (%d menit).',
                        $existingLesson->learningClass->name,
                        Carbon::parse($existingLesson->lesson_date)->locale('id')->translatedFormat('l, d F Y'),
                        substr($existingLesson->lesson_time, 0, 5),
                        $existingLesson->duration_minutes,
                    ),
                ]);
            }
        }
    }

    private function timestampInMinutes(string $date, string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return (int) (Carbon::parse($date)->startOfDay()->timestamp / 60) + ($hours * 60) + $minutes;
    }
}
