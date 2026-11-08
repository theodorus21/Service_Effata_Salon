@extends('layouts.dashboard')

@section('title', 'Laporan Kelas')
@section('breadcrumb', 'Jadwal Kelas / Laporan Kelas')

@php($lessonDate = \Illuminate\Support\Carbon::parse($lesson->lesson_date))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Laporan per Murid</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->students->pluck('name')->join(', ') }} · Guru {{ $learningClass->teacher->name }}</p>
        </div>
        @if (auth()->user()->hasPermission('class_schedules.view'))
            <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $lessonDate->month, 'year' => $lessonDate->year]) }}">Kembali</a>
        @endif
    </section>

    <section class="panel lesson-student-report-list">
        <div class="reschedule-current">
            <strong>Jadwal kelas</strong>
            <span>{{ $lessonDate->locale('id')->translatedFormat('l, d F Y') }} · {{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit</span>
            <span>Absensi guru: {{ $lesson->teacher_attendance_at ? \Illuminate\Support\Carbon::parse($lesson->teacher_attendance_at)->locale('id')->translatedFormat('d F Y H:i') : 'Belum tercatat' }}</span>
        </div>
        @if (session('status'))
            <div class="master-data-alert" role="status">{{ session('status') }}</div>
        @endif
        <div class="lesson-student-report-grid">
            @foreach ($lesson->studentRecords as $studentRecord)
                <article class="lesson-student-report-card">
                    <div>
                        <h2>{{ $studentRecord->student->name }}</h2>
                        <p>Absensi murid:
                            {{ $studentRecord->attendance_status ? \App\Models\LearningClassLessonStudent::ATTENDANCE_STATUSES[$studentRecord->attendance_status] : 'Belum diabsen' }}
                        </p>
                        @if ($studentRecord->reason)
                            <p>Alasan tidak hadir: {{ $studentRecord->reason }}</p>
                        @endif
                        @if ($studentRecord->attendance_status && $studentRecord->attendance_status !== 'present')
                            <p>Laporan pelajaran tidak diperlukan untuk murid yang tidak hadir.</p>
                        @else
                            <p>{{ $studentRecord->lesson_description ? 'Laporan sudah diisi.' : 'Laporan belum diisi.' }}</p>
                        @endif
                    </div>
                    @if (auth()->user()->hasPermission('class_schedules.report'))
                        <a class="primary-action" href="{{ route('class-schedules.lessons.report.student.edit', ['learningClass' => $learningClass, 'lesson' => $lesson, 'studentRecord' => $studentRecord]) }}">
                            @if ($studentRecord->attendance_status && $studentRecord->attendance_status !== 'present')
                                Lihat / ubah notes
                            @else
                                {{ $studentRecord->lesson_description ? 'Ubah laporan' : 'Isi laporan' }}
                            @endif
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endsection
