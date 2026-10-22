@extends('layouts.dashboard')

@section('title', 'Detail Jadwal Les')
@section('breadcrumb', 'Jadwal Kelas / Detail Sesi')

@php($lessonDate = \Illuminate\Support\Carbon::parse($lesson->lesson_date))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>Detail Jadwal Les</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->student->name }} · Guru {{ $learningClass->teacher->name }}</p>
        </div>
        <a class="secondary-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $lessonDate->month, 'year' => $lessonDate->year]) }}">Kembali ke daftar</a>
    </section>

    <section class="panel lesson-detail-panel" aria-label="Detail record jadwal les">
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
                <dt>Nama murid</dt>
                <dd>{{ $learningClass->student->name }}</dd>
            </div>
            <div>
                <dt>Nama guru</dt>
                <dd>{{ $learningClass->teacher->name }}</dd>
            </div>
            <div>
                <dt>Absen guru</dt>
                <dd>{{ $lesson->teacher_attendance_at ? \Illuminate\Support\Carbon::parse($lesson->teacher_attendance_at)->locale('id')->translatedFormat('l, d F Y H:i') : '-' }}</dd>
            </div>
            <div class="lesson-detail-full-width">
                <dt>Foto pelajaran</dt>
                <dd>
                    @if ($lesson->lesson_photo && auth()->user()->hasPermission('class_schedules.photo'))
                        <a href="{{ route('class-schedules.lessons.photo', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}" target="_blank" rel="noopener">Lihat foto pelajaran</a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="lesson-detail-full-width">
                <dt>Deskripsi pelajaran</dt>
                <dd>{{ $lesson->lesson_description ?: '-' }}</dd>
            </div>
            <div class="lesson-detail-full-width">
                <dt>Notes</dt>
                <dd>{{ $lesson->notes ?: '-' }}</dd>
            </div>
        </dl>

        <details class="lesson-history-section">
            <summary>Riwayat Pindah Jadwal</summary>
            @forelse ($lesson->rescheduleHistories as $history)
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
                    <small>{{ $history->updatedBy?->name ?? 'Pengguna tidak tercatat' }} · {{ $history->created_at->locale('id')->translatedFormat('d F Y H:i') }}</small>
                </div>
            @empty
                <p class="lesson-history-empty">-</p>
            @endforelse
        </details>
    </section>
@endsection
