@extends('layouts.dashboard')

@section('title', 'Jadwal Kelas')
@section('breadcrumb', 'Jadwal Kelas')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>Jadwal Kelas</h1>
            <p>Jadwal berulang dari setiap kelas untuk {{ $month->locale('id')->translatedFormat('F Y') }}.</p>
        </div>
    </section>

    <section class="panel schedule-filter-panel" aria-label="Filter jadwal kelas">
        <form class="schedule-filter-form" method="GET" action="{{ route('class-schedules.index') }}">
            @if ($showTeacherFilter)
                <div class="master-data-field">
                    <label for="teacher_id">Nama guru</label>
                    <select id="teacher_id" name="teacher_id">
                        <option value="">Semua guru</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($showStudentFilter)
                <div class="master-data-field">
                    <label for="student_id">Nama murid</label>
                    <select id="student_id" name="student_id">
                        <option value="">Semua murid</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="master-data-field">
                <label for="month">Bulan</label>
                <input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}">
            </div>
            <div class="master-data-field">
                <label for="day">Hari</label>
                <select id="day" name="day">
                    <option value="">Semua hari</option>
                    @foreach ($days as $dayKey => $dayLabel)
                        <option value="{{ $dayKey }}" @selected(request('day') === $dayKey)>{{ $dayLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="schedule-filter-actions">
                <button class="primary-action" type="submit">Terapkan filter</button>
                <a class="secondary-action" href="{{ route('class-schedules.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel master-data-panel learning-class-list-panel" aria-label="Daftar jadwal kelas">
        <div class="panel-heading">
            <div>
                <h2>Daftar Jadwal Kelas</h2>
                <p>{{ $classes->total() }} kelas · jadwal {{ $month->locale('id')->translatedFormat('F Y') }}</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama kelas</th>
                        <th>Nama murid</th>
                        <th>Program</th>
                        <th>Hari &amp; jam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $learningClass)
                        <tr>
                            <td><strong>{{ $learningClass->name }}</strong></td>
                            <td>{{ $learningClass->student->name }}</td>
                            <td>{{ $learningClass->program->value }}</td>
                            <td class="class-schedules-cell">
                                <div class="class-schedule-list">
                                    @foreach ($learningClass->schedules->when(request('day'), fn ($schedules) => $schedules->where('day', request('day'))) as $schedule)
                                        <div class="class-schedule-item">
                                            <span class="class-schedule-day">{{ $days[$schedule->day] }}</span>
                                            <span class="class-schedule-time">
                                                {{ substr($schedule->lesson_time, 0, 5) }}–{{ \Carbon\Carbon::parse($schedule->lesson_time)->addMinutes($schedule->duration_minutes)->format('H:i') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <a class="table-action" href="{{ route('class-schedules.show', ['learningClass' => $learningClass, 'month' => $month->month, 'year' => $month->year]) }}">Lihat jadwal</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="5">Tidak ada jadwal kelas yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($classes->hasPages())
            <div class="master-data-pagination">{{ $classes->links() }}</div>
        @endif
    </section>
@endsection
