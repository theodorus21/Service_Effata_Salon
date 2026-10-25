@extends('layouts.dashboard')

@section('title', 'Kelas')
@section('breadcrumb', 'Kelas')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>Daftar Kelas</h1>
            <p>Kelola murid, guru, program, dan jadwal kelas.</p>
        </div>
        <div class="learning-class-heading-actions">
            @if (auth()->user()->hasPermission('learning_classes.create'))
            <a class="primary-action" href="{{ route('learning-classes.create') }}">
                <span aria-hidden="true">+</span> Tambah Kelas
            </a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif

    <section class="panel master-data-panel learning-class-list-panel" aria-label="Daftar kelas">
        <div class="panel-heading">
            <div>
                <h2>Daftar Kelas</h2>
                <p>{{ $classes->total() }} kelas terdaftar</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Nama kelas</th>
                        <th>Murid &amp; guru</th>
                        <th>Program</th>
                        <th>Kategori</th>
                        <th>Jadwal kelas</th>
                        <th class="class-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $learningClass)
                        <tr>
                            <td>
                                @if (auth()->user()->hasPermission('learning_classes.show'))
                                    <a class="master-data-name-link" href="{{ route('learning-classes.show', $learningClass) }}">{{ $learningClass->name }}</a>
                                @else
                                    <strong>{{ $learningClass->name }}</strong>
                                @endif
                            </td>
                            <td>
                                <div class="class-people-cell">
                                    <strong>{{ $learningClass->student->name }}</strong>
                                    <small>Guru: {{ $learningClass->teacher->name }}</small>
                                </div>
                            </td>
                            <td>{{ $learningClass->program->value }}</td>
                            <td>{{ $learningClass->category->value }}</td>
                            <td class="class-schedules-cell">
                                <div class="class-schedule-list">
                                    @foreach ($learningClass->schedules as $schedule)
                                        <div class="class-schedule-item">
                                            <span class="class-schedule-day">{{ $days[$schedule->day] }}</span>
                                            <span class="class-schedule-time">
                                                {{ substr($schedule->lesson_time, 0, 5) }}–{{ \Carbon\Carbon::parse($schedule->lesson_time)->addMinutes($schedule->duration_minutes)->format('H:i') }}
                                            </span>
                                            <span class="class-schedule-duration">{{ $schedule->duration_minutes }} mnt</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="learning-class-actions-cell">
                                <div class="learning-class-actions">
                                    @if (auth()->user()->hasPermission('learning_classes.show'))
                                        <a class="table-action" href="{{ route('learning-classes.show', $learningClass) }}">Detail</a>
                                    @endif
                                    @if (auth()->user()->hasPermission('class_reports.view'))
                                        <a class="table-action" href="{{ route('learning-classes.reports.index', $learningClass) }}">Report</a>
                                    @endif
                                    @if (auth()->user()->hasPermission('learning_classes.update'))
                                        <a class="table-action" href="{{ route('learning-classes.edit', $learningClass) }}">Ubah</a>
                                    @endif
                                    @if (auth()->user()->hasPermission('learning_classes.generate'))
                                        <a class="table-action table-action-generate" href="{{ route('learning-classes.lessons.generate.form', $learningClass) }}">Generate jadwal</a>
                                    @endif
                                    @if (auth()->user()->hasPermission('learning_classes.delete'))
                                    <form method="POST" action="{{ route('learning-classes.destroy', $learningClass) }}" onsubmit="return confirm('Hapus kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="table-action table-action-danger" type="submit">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="6">Belum ada kelas.</td>
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
