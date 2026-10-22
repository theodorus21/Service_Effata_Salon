@extends('layouts.dashboard')

@section('title', 'Laporan Kelas')
@section('breadcrumb', 'Jadwal Kelas / Laporan Kelas')

@php($lessonDate = \Illuminate\Support\Carbon::parse($lesson->lesson_date))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Laporan Kelas</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->student->name }} · Guru {{ $learningClass->teacher->name }}</p>
        </div>
        @if (auth()->user()->hasPermission('class_schedules.view'))
            <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $lessonDate->month, 'year' => $lessonDate->year]) }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <div class="reschedule-current">
            <strong>Jadwal kelas</strong>
            <span>{{ $lessonDate->locale('id')->translatedFormat('l, d F Y') }} · {{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit</span>
            <span>Absensi guru: {{ $lesson->teacher_attendance_at ? \Illuminate\Support\Carbon::parse($lesson->teacher_attendance_at)->locale('id')->translatedFormat('d F Y H:i') : 'Belum tercatat' }}</span>
        </div>
        <form method="POST" action="{{ route('class-schedules.lessons.report.update', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}" enctype="multipart/form-data">
            @csrf
            <div class="master-data-field">
                <label for="lesson_photo">Foto pelajaran <span aria-hidden="true">*</span></label>
                <input id="lesson_photo" name="lesson_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                <small>Format JPG, PNG, atau WebP; ukuran maksimal 5 MB.</small>
                @error('lesson_photo')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="lesson_description">Deskripsi pelajaran <span aria-hidden="true">*</span></label>
                <textarea id="lesson_description" name="lesson_description" rows="5" maxlength="5000" required>{{ old('lesson_description') }}</textarea>
                @error('lesson_description')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="notes">Notes <span>(opsional)</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="5000">{{ old('notes') }}</textarea>
                @error('notes')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('class_schedules.view'))
                    <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $lessonDate->month, 'year' => $lessonDate->year]) }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">Simpan laporan kelas</button>
            </div>
        </form>
    </section>
@endsection
