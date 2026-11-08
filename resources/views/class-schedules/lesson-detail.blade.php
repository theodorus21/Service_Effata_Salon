@extends('layouts.dashboard')

@section('title', 'Detail Jadwal Les')
@section('breadcrumb', 'Jadwal Kelas / Detail Sesi')

@php($lessonDate = \Illuminate\Support\Carbon::parse($lesson->lesson_date))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Detail Jadwal Les</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->students->pluck('name')->join(', ') }} · Guru {{ $lesson->teacher?->name ?? $learningClass->teacher->name }}</p>
        </div>
        <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $lessonDate->month, 'year' => $lessonDate->year]) }}">Kembali ke daftar</a>
    </section>

    <section class="panel lesson-detail-panel" aria-label="Detail record jadwal les">
        @if (session('status'))
            <div class="master-data-alert" role="status">{{ session('status') }}</div>
        @endif
        <div class="lesson-detail-heading">
            <div>
                <p class="dashboard-eyebrow">INFORMASI SESI</p>
                <h2>{{ $lessonDate->locale('id')->translatedFormat('l, d F Y') }}</h2>
                <p>{{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit</p>
            </div>
            <span class="lesson-status-badge" style="--lesson-status-color: {{ $lesson->lessonStatus->color }}">{{ $lesson->lessonStatus->label }}</span>
        </div>

        <dl class="lesson-detail-grid">
            <div>
                <dt>Program dan kategori</dt>
                <dd>{{ $learningClass->program->value }} · {{ $learningClass->category->value }}</dd>
            </div>
            <div>
                <dt>Nama guru</dt>
                <dd>{{ $lesson->teacher?->name ?? $learningClass->teacher->name }}</dd>
            </div>
            <div>
                <dt>Absen guru</dt>
                <dd>{{ $lesson->teacher_attendance_at ? \Illuminate\Support\Carbon::parse($lesson->teacher_attendance_at)->locale('id')->translatedFormat('l, d F Y H:i') : '-' }}</dd>
            </div>
            @if ($lesson->reason)
                <div>
                    <dt>Alasan pembatalan</dt>
                    <dd>{{ $lesson->reason }}</dd>
                </div>
            @endif
        </dl>

        <section class="lesson-student-record-list" aria-label="Detail masing-masing murid">
            <h2>Detail per murid</h2>
            @forelse ($lesson->studentRecords as $studentRecord)
                <article class="lesson-student-record-card">
                    <div class="lesson-student-record-heading">
                        <h3>{{ $studentRecord->student->name }}</h3>
                        <span class="lesson-attendance-badge">
                            {{ $studentRecord->attendance_status ? \App\Models\LearningClassLessonStudent::ATTENDANCE_STATUSES[$studentRecord->attendance_status] : 'Belum diabsen' }}
                        </span>
                    </div>
                    <dl class="lesson-detail-grid">
                        <div>
                            <dt>Deskripsi pelajaran</dt>
                            <dd>{{ $studentRecord->lesson_description ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt>Notes</dt>
                            <dd>{{ $studentRecord->notes ?: '-' }}</dd>
                        </div>
                        @if ($studentRecord->reason && $studentRecord->attendance_status !== 'present')
                            <div>
                                <dt>Alasan tidak hadir</dt>
                                <dd>{{ $studentRecord->reason }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Foto pelajaran</dt>
                            <dd>
                                @if ($studentRecord->lesson_photo && auth()->user()->hasPermission('class_schedules.photo'))
                                    <a href="{{ route('class-schedules.lessons.photo', ['learningClass' => $learningClass, 'lesson' => $lesson, 'studentRecord' => $studentRecord]) }}" target="_blank" rel="noopener">Lihat foto pelajaran</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Absensi dicatat</dt>
                            <dd>{{ $studentRecord->attendance_at ? \Illuminate\Support\Carbon::parse($studentRecord->attendance_at)->locale('id')->translatedFormat('d F Y H:i') : '-' }}</dd>
                        </div>
                    </dl>
                    @if ($lesson->lessonStatus->system_key === 'sedang_berlangsung' && auth()->user()->hasPermission('class_schedules.report'))
                        <a class="secondary-action" href="{{ route('class-schedules.lessons.report.student.edit', ['learningClass' => $learningClass, 'lesson' => $lesson, 'studentRecord' => $studentRecord]) }}">Isi / ubah laporan murid</a>
                    @endif
                </article>
            @empty
                <p class="master-data-empty">Belum ada murid yang terdaftar pada lesson ini.</p>
            @endforelse
        </section>

        @if ($lesson->rescheduleHistories->isNotEmpty())
            <details class="lesson-history-section">
                <summary>Riwayat Pindah Jadwal</summary>
                @foreach ($lesson->rescheduleHistories as $history)
                <div class="lesson-history-item">
                    <div>
                        <strong>{{ \Illuminate\Support\Carbon::parse($history->old_lesson_date)->locale('id')->translatedFormat('d F Y') }} · {{ substr($history->old_lesson_time, 0, 5) }}</strong>
                        <span>Jadwal sebelumnya</span>
                    </div>
                    <span class="lesson-history-arrow" aria-hidden="true">→</span>
                    <div>
                        <strong>{{ \Illuminate\Support\Carbon::parse($history->new_lesson_date)->locale('id')->translatedFormat('d F Y') }} · {{ substr($history->new_lesson_time, 0, 5) }}</strong>
                        <span>Jadwal baru</span>
                    </div>
                    <div>
                        <strong>{{ $history->oldTeacher?->name ?? '-' }} → {{ $history->newTeacher?->name ?? '-' }}</strong>
                        <span>Guru</span>
                    </div>
                    <p class="lesson-history-reason">{{ $history->reason ?: '-' }}</p>
                    <small class="lesson-history-meta">{{ $history->updatedBy?->name ?? 'Pengguna tidak tercatat' }} · {{ $history->created_at->locale('id')->translatedFormat('d F Y H:i') }}</small>
                </div>
                @endforeach
            </details>
        @endif
    </section>
@endsection
