@extends('layouts.dashboard')

@section('title', 'Pindah Jadwal Les')
@section('breadcrumb', 'Jadwal Kelas / Pindah Jadwal')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Pindah Jadwal Les</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->student->name }} · Guru {{ $lesson->teacher?->name ?? $learningClass->teacher->name }}</p>
        </div>
        @if (auth()->user()->hasPermission('class_schedules.view'))
            <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => \Illuminate\Support\Carbon::parse($lesson->lesson_date)->month, 'year' => \Illuminate\Support\Carbon::parse($lesson->lesson_date)->year]) }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <div class="reschedule-current">
            <strong>Jadwal saat ini</strong>
            <span>{{ \Illuminate\Support\Carbon::parse($lesson->lesson_date)->locale('id')->translatedFormat('l, d F Y') }} · {{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit</span>
        </div>
        <form method="POST" action="{{ route('class-schedules.lessons.reschedule.update', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}">
            @csrf
            @method('PUT')
            <div class="master-data-field">
                <label for="lesson_date">Tanggal baru</label>
                <input id="lesson_date" name="lesson_date" type="date" value="{{ old('lesson_date', \Illuminate\Support\Carbon::parse($lesson->lesson_date)->format('Y-m-d')) }}" required>
                @error('lesson_date')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="lesson_time">Jam baru</label>
                <input id="lesson_time" name="lesson_time" type="time" value="{{ old('lesson_time', substr($lesson->lesson_time, 0, 5)) }}" required>
                @error('lesson_time')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="duration_minutes">Durasi les (menit)</label>
                <input id="duration_minutes" name="duration_minutes" type="number" value="{{ old('duration_minutes', $lesson->duration_minutes) }}" required min="1" max="1440" step="1">
                @error('duration_minutes')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="teacher_id">Guru</label>
                <select id="teacher_id" name="teacher_id" required>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $lesson->teacher_id ?? $learningClass->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                @error('teacher_id')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="reason">Alasan pindah jadwal</label>
                <textarea id="reason" name="reason" rows="4" maxlength="5000" required>{{ old('reason') }}</textarea>
                @error('reason')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('class_schedules.view'))
                    <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => \Illuminate\Support\Carbon::parse($lesson->lesson_date)->month, 'year' => \Illuminate\Support\Carbon::parse($lesson->lesson_date)->year]) }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">Simpan jadwal baru</button>
            </div>
        </form>
    </section>
@endsection
