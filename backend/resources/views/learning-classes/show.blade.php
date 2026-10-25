@extends('layouts.dashboard')

@section('title', 'Detail Kelas')
@section('breadcrumb', 'Kelas / Detail')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / KELAS</p>
            <h1>Detail Kelas</h1>
            <p>Informasi lengkap kelas dan jadwal les.</p>
        </div>
        <div class="master-data-heading-actions">
            @if (auth()->user()->hasPermission('learning_classes.view'))
                <a class="secondary-action" href="{{ route('learning-classes.index') }}">Kembali</a>
            @endif
            @if (auth()->user()->hasPermission('learning_classes.update'))
                <a class="primary-action" href="{{ route('learning-classes.edit', $learningClass) }}">Ubah kelas</a>
            @endif
        </div>
    </section>

    <section class="panel guru-detail-panel">
        <div class="guru-detail-heading">
            <span class="avatar avatar-plum">{{ mb_strtoupper(mb_substr($learningClass->name, 0, 1)) }}</span>
            <div>
                <h2>{{ $learningClass->name }}</h2>
                <p>{{ $learningClass->category->value }} · {{ $learningClass->program->value }}</p>
            </div>
        </div>

        <dl class="guru-detail-grid">
            <div><dt>Nama kelas</dt><dd>{{ $learningClass->name }}</dd></div>
            <div><dt>Program</dt><dd>{{ $learningClass->program->value }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $learningClass->category->value }}</dd></div>
            <div><dt>Nama murid</dt><dd>{{ $learningClass->student->name }}</dd></div>
            <div><dt>Nama guru</dt><dd>{{ $learningClass->teacher->name }}</dd></div>
            <div>
                <dt>Jadwal</dt>
                <dd>
                    @if ($learningClass->isMonthly())
                        @foreach ($learningClass->schedules as $schedule)
                            {{ $days[$schedule->day] }} · {{ substr($schedule->lesson_time, 0, 5) }} · {{ $schedule->duration_minutes }} menit<br>
                        @endforeach
                    @else
                        @foreach ($learningClass->lessons as $lesson)
                            {{ \Illuminate\Support\Carbon::parse($lesson->lesson_date)->format('d/m/Y') }} · {{ substr($lesson->lesson_time, 0, 5) }} · {{ $lesson->duration_minutes }} menit<br>
                        @endforeach
                    @endif
                </dd>
            </div>
        </dl>
    </section>
@endsection
