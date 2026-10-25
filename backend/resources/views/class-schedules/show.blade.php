@extends('layouts.dashboard')

@section('title', 'Daftar Jadwal Kelas')
@section('breadcrumb', 'Jadwal Kelas / Daftar Sesi')
@section('scripts')
    @vite('resources/js/class-schedules.js')
@endsection

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / JADWAL KELAS</p>
            <h1>{{ $learningClass->name }}</h1>
            <p>{{ $learningClass->student->name }} · Guru {{ $learningClass->teacher->name }}</p>
        </div>
        <a class="secondary-action" href="{{ route('class-schedules.index', ['month' => sprintf('%04d-%02d', $year, $month)]) }}">Kembali ke jadwal kelas</a>
    </section>

    <section class="panel lesson-list-panel" aria-label="Daftar sesi les">
        <div class="lesson-list-heading">
            <div>
                <h2>Daftar Sesi Les</h2>
                <p>{{ $learningClass->program->value }} · {{ $learningClass->category->value }}</p>
            </div>
            <form class="lesson-period-form" method="GET" action="{{ route('class-schedules.show', $learningClass) }}">
                <div>
                    <label for="lesson-month">Bulan</label>
                    <select id="lesson-month" name="month">
                        @foreach (range(1, 12) as $monthNumber)
                            <option value="{{ $monthNumber }}" @selected($month === $monthNumber)>{{ \Carbon\Carbon::create()->month($monthNumber)->locale('id')->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="lesson-year">Tahun</label>
                    <input id="lesson-year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" required>
                </div>
                <button class="secondary-action" type="submit">Tampilkan</button>
            </form>
        </div>
        <h3 class="lesson-period-title">{{ \Carbon\Carbon::create()->month($month)->locale('id')->translatedFormat('F') }} {{ $year }}</h3>
        @if (session('status'))
            <div class="master-data-alert" role="status">{{ session('status') }}</div>
        @endif
        <div class="table-scroll">
            <table class="lesson-record-table">
                <thead>
                    <tr>
                        <th>Tanggal les</th>
                        <th>Jam les</th>
                        <th>Guru</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lessons as $lesson)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($lesson->lesson_date)->locale('id')->translatedFormat('l, d F Y') }}</td>
                            <td>{{ substr($lesson->lesson_time, 0, 5) }}</td>
                            <td>{{ $lesson->teacher?->name ?? $learningClass->teacher->name }}</td>
                            <td>{{ $lesson->duration_minutes }} menit</td>
                            <td>
                                <span class="lesson-status-badge" style="--lesson-status-color: {{ $lesson->lessonStatus->color }}">
                                    {{ $lesson->lessonStatus->label }}
                                </span>
                            </td>
                            <td>
                                <div class="lesson-row-actions">
                                    @if (auth()->user()->hasPermission('class_schedules.view'))
                                    <a class="lesson-icon-action lesson-detail-action" href="{{ route('class-schedules.lessons.show', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}" aria-label="Detail jadwal" title="Detail jadwal">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7.5"></circle><path d="m16.5 16.5 4 4M11 8v.01M11 11v4"></path></svg>
                                        <span>Detail</span>
                                    </a>
                                    @endif
                                    @if ($lesson->lessonStatus->system_key === 'belum_terlaksana')
                                        @if (auth()->user()->hasPermission('class_schedules.attendance'))
                                        <form method="POST" action="{{ route('class-schedules.lessons.attendance', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}">
                                            @csrf
                                            <button class="lesson-icon-action lesson-attendance-action" type="submit" aria-label="Absen guru" title="Absen guru">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.5 19 6v5.5c0 4.2-2.8 7.4-7 9-4.2-1.6-7-4.8-7-9V6l7-2.5Z"></path><path d="m8.5 11.5 2.3 2.3 4.7-4.7"></path></svg>
                                                <span>Absen guru</span>
                                            </button>
                                        </form>
                                        @endif
                                        @if (auth()->user()->hasPermission('class_schedules.reschedule'))
                                        <a class="lesson-icon-action lesson-reschedule-action" href="{{ route('class-schedules.lessons.reschedule.edit', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}" aria-label="Pindah jadwal" title="Pindah jadwal">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h13M14 4l3 3-3 3M20 17H7m3-3-3 3 3 3"></path></svg>
                                            <span>Pindah jadwal</span>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('class_schedules.cancel'))
                                        <button class="lesson-icon-action lesson-cancel-action" type="button" data-open-cancel-dialog="cancel-lesson-{{ $lesson->id }}" aria-label="Cancel kelas" title="Cancel kelas">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="m9 9 6 6m0-6-6 6"></path></svg>
                                            <span>Cancel kelas</span>
                                        </button>
                                        @endif
                                    @elseif ($lesson->lessonStatus->system_key === 'sedang_berlangsung')
                                        @if (auth()->user()->hasPermission('class_schedules.report'))
                                        <a class="lesson-icon-action lesson-report-action" href="{{ route('class-schedules.lessons.report.edit', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}" aria-label="Laporan Kelas" title="Laporan Kelas">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3.5h9l5 5v12H5z"></path><path d="M14 3.5v5h5M8 13h8M8 16.5h8"></path></svg>
                                            <span>Laporan Kelas</span>
                                        </a>
                                        @endif
                                    @else
                                        @if ($lesson->lessonStatus->system_key === 'dibatalkan')
                                            <span class="lesson-cancelled-label">Kelas dibatalkan</span>
                                        @endif
                                    @endif
                                </div>
                                @if ($lesson->lessonStatus->system_key === 'belum_terlaksana' && auth()->user()->hasPermission('class_schedules.cancel'))
                                    <dialog class="lesson-cancel-dialog" id="cancel-lesson-{{ $lesson->id }}" aria-labelledby="cancel-lesson-title-{{ $lesson->id }}" @if ($errors->has('reason') && (string) old('cancel_lesson_id') === (string) $lesson->id) data-open-on-load @endif>
                                        <form method="POST" action="{{ route('class-schedules.lessons.cancel', ['learningClass' => $learningClass, 'lesson' => $lesson]) }}">
                                            @csrf
                                            <input type="hidden" name="cancel_lesson_id" value="{{ $lesson->id }}">
                                            <div class="lesson-cancel-dialog-heading">
                                                <div>
                                                    <h2 id="cancel-lesson-title-{{ $lesson->id }}">Batalkan kelas?</h2>
                                                    <p>{{ \Carbon\Carbon::parse($lesson->lesson_date)->locale('id')->translatedFormat('l, d F Y') }} · {{ substr($lesson->lesson_time, 0, 5) }}</p>
                                                </div>
                                                <button class="lesson-dialog-close" type="button" data-close-cancel-dialog aria-label="Tutup dialog">&times;</button>
                                            </div>
                                            <label for="cancellation-reason-{{ $lesson->id }}">Alasan pembatalan</label>
                                            <textarea id="cancellation-reason-{{ $lesson->id }}" name="reason" rows="4" maxlength="5000" required placeholder="Tuliskan alasan pembatalan">{{ (string) old('cancel_lesson_id') === (string) $lesson->id ? old('reason') : '' }}</textarea>
                                            @if ((string) old('cancel_lesson_id') === (string) $lesson->id)
                                                @error('reason')<p class="master-data-error">{{ $message }}</p>@enderror
                                            @endif
                                            <div class="lesson-cancel-dialog-actions">
                                                <button class="secondary-action" type="button" data-close-cancel-dialog>Kembali</button>
                                                <button class="lesson-cancel-submit" type="submit">Konfirmasi pembatalan</button>
                                            </div>
                                        </form>
                                    </dialog>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="4">Belum ada record jadwal les untuk bulan dan tahun ini. Generate jadwal dari daftar kelas terlebih dahulu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($lessons->hasPages())
            <div class="master-data-pagination">{{ $lessons->links() }}</div>
        @endif
        <div class="lesson-schedule-summary">
            <strong>Jadwal berulang kelas</strong>
            @foreach ($learningClass->schedules as $schedule)
                <span>{{ ucfirst($schedule->day) }} · {{ substr($schedule->lesson_time, 0, 5) }} · {{ $schedule->duration_minutes }} menit</span>
            @endforeach
        </div>
    </section>
@endsection
