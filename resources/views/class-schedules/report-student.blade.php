@extends('layouts.dashboard')

@section('title', 'Laporan Murid')
@section('breadcrumb', 'Jadwal Kelas / Laporan Murid')

@php($lessonDate = \Illuminate\Support\Carbon::parse($lesson->lesson_date))
@php($selectedAttendanceStatus = $studentRecord->attendance_at ? $studentRecord->attendance_status : old('attendance_status'))
@php($hasSelectedAttendanceStatus = is_string($selectedAttendanceStatus) && array_key_exists($selectedAttendanceStatus, $attendanceStatuses))
@php($requiresLessonContent = $selectedAttendanceStatus === 'present')
@php($requiresAbsenceReason = $hasSelectedAttendanceStatus && ! $requiresLessonContent)

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Laporan Murid</h1>
            <p>{{ $learningClass->name }} · {{ $studentRecord->student->name }} · Guru {{ $lesson->teacher?->name ?? $learningClass->teacher->name }}</p>
        </div>
        <a class="secondary-action" href="{{ route('class-schedules.lessons.report.edit', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}">Kembali ke daftar murid</a>
    </section>

    <section class="panel master-data-form-panel">
        <div class="reschedule-current">
            <strong>Jadwal kelas</strong>
            <span>{{ $lessonDate->locale('id')->translatedFormat('l, d F Y') }} · {{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit</span>
            <span>Status lesson: {{ $lesson->lessonStatus->label }}</span>
            <span>Status absensi murid: {{ $studentRecord->attendance_status ? $attendanceStatuses[$studentRecord->attendance_status] : 'Belum diabsen' }}</span>
        </div>
        <form method="POST" action="{{ route('class-schedules.lessons.report.update', ['learningClass' => $learningClass, 'lesson' => $lesson, 'studentRecord' => $studentRecord]) }}" enctype="multipart/form-data" data-student-report-form data-attendance-status="{{ $studentRecord->attendance_status ?? '' }}">
            @csrf
            @if ($studentRecord->attendance_at)
                <div class="reschedule-current">
                    <strong>Absensi sudah dikirim dan tidak dapat diubah</strong>
                    <span>Status: {{ $attendanceStatuses[$studentRecord->attendance_status] }}</span>
                    @if ($studentRecord->reason)
                        <span>Alasan tidak hadir: {{ $studentRecord->reason }}</span>
                    @endif
                </div>
            @else
                <div class="master-data-field">
                    <label for="attendance_status">Status absensi <span aria-hidden="true">*</span></label>
                    <select id="attendance_status" name="attendance_status" data-attendance-select required>
                        <option value="" disabled @selected(old('attendance_status') === null)>Pilih status absensi</option>
                        @foreach ($attendanceStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('attendance_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('attendance_status')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div class="master-data-field">
                    <label for="reason">Alasan tidak hadir <span>(wajib selain status hadir)<span data-attendance-required-marker="absence" @if (! $requiresAbsenceReason) hidden @endif aria-hidden="true">*</span></span></label>
                    <textarea id="reason" name="reason" rows="3" maxlength="5000" data-attendance-dependent="absence" data-required-if-enabled="true" @disabled(! $requiresAbsenceReason) @required($requiresAbsenceReason)>{{ old('reason') }}</textarea>
                    @error('reason')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
            @endif
            <div class="master-data-field">
                <label for="lesson_photo">Foto pelajaran <span data-attendance-required-marker="present" data-has-value="{{ $studentRecord->lesson_photo ? 'true' : 'false' }}" @if (! $requiresLessonContent || $studentRecord->lesson_photo) hidden @endif aria-hidden="true">*</span></label>
                <input id="lesson_photo" name="lesson_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-attendance-dependent="present" data-required-if-enabled="{{ $studentRecord->lesson_photo ? 'false' : 'true' }}" @disabled(! $requiresLessonContent) @required($requiresLessonContent && ! $studentRecord->lesson_photo)>
                <small>Diisi hanya jika status hadir. Format JPG, PNG, atau WebP; ukuran maksimal 5 MB.</small>
                @if ($studentRecord->lesson_photo)
                    <small>Foto sebelumnya sudah tersimpan.</small>
                @endif
                @error('lesson_photo')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="lesson_description">Deskripsi pelajaran <span data-attendance-required-marker="present" @if (! $requiresLessonContent) hidden @endif aria-hidden="true">*</span></label>
                <textarea id="lesson_description" name="lesson_description" rows="5" maxlength="5000" data-attendance-dependent="present" data-required-if-enabled="true" @disabled(! $requiresLessonContent) @required($requiresLessonContent)>{{ old('lesson_description', $studentRecord->lesson_description) }}</textarea>
                @error('lesson_description')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="notes">Notes <span>(opsional)</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="5000">{{ old('notes', $studentRecord->notes) }}</textarea>
                @error('notes')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                <a class="secondary-action" href="{{ route('class-schedules.lessons.report.edit', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}">Batal</a>
                <button class="primary-action" type="submit">Simpan laporan murid</button>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    @vite('resources/js/class-schedules.js')
@endsection
