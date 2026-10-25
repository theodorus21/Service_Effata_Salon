@extends('layouts.dashboard')

@section('title', 'Report Kelas')
@section('breadcrumb', 'Kelas / Report')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / REPORT</p>
            <h1>Report {{ $learningClass->name }}</h1>
            <p>Daftar report pembelajaran untuk kelas ini.</p>
        </div>
        <div class="master-data-heading-actions">
            <a class="secondary-action" href="{{ route('learning-classes.index') }}">Kembali ke kelas</a>
            @if (auth()->user()->hasPermission('class_reports.create'))
                <a class="primary-action" href="{{ route('learning-classes.reports.create', ['learningClass' => $learningClass, ...$period]) }}">
                    <span aria-hidden="true">+</span> Tambah Report
                </a>
            @endif
        </div>
    </section>

    <section class="panel lesson-list-panel">
        <form class="lesson-period-form" method="GET" action="{{ route('learning-classes.reports.index', $learningClass) }}">
            <div>
                <label for="period_start">Periode mulai</label>
                <input id="period_start" name="period_start" type="month" value="{{ $period['period_start'] }}" required>
            </div>
            <div>
                <label for="period_end">Periode sampai</label>
                <input id="period_end" name="period_end" type="month" value="{{ $period['period_end'] }}" required>
            </div>
            <button class="primary-action" type="submit">Cari Report</button>
        </form>
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif

    <section class="panel master-data-panel learning-class-list-panel" aria-label="Daftar report kelas">
        <div class="panel-heading">
            <div>
                <h2>Daftar Report</h2>
                <p>{{ $reports->total() }} report terdaftar</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Guru</th>
                        <th>Unduh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>
                                @if ($report->period_start === $report->period_end)
                                    {{ \Carbon\Carbon::createFromFormat('!Y-m', $report->period_start)->locale('id')->translatedFormat('F Y') }}
                                @else
                                    {{ \Carbon\Carbon::createFromFormat('!Y-m', $report->period_start)->locale('id')->translatedFormat('F Y') }}
                                    –
                                    {{ \Carbon\Carbon::createFromFormat('!Y-m', $report->period_end)->locale('id')->translatedFormat('F Y') }}
                                @endif
                            </td>
                            <td>{{ $report->teacher->name }}</td>
                            <td>
                                <a class="secondary-action report-download-button" href="{{ route('learning-classes.reports.download', ['learningClass' => $learningClass, 'report' => $report]) }}">
                                    Unduh PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-data-empty" colspan="3">Tidak ada report pada periode yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())
            <div class="master-data-pagination">{{ $reports->links() }}</div>
        @endif
    </section>
@endsection
