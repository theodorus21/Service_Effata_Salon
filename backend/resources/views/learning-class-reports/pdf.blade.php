<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Report Pembelajaran</title>
    <style>
        @page {
            margin: 32px 38px 42px;
        }

        body {
            color: #24445d;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.5;
        }

        .header {
            border-bottom: 2px solid #386d9a;
            margin-bottom: 20px;
            padding-bottom: 12px;
            width: 100%;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            height: 58px;
            margin-right: 14px;
            width: 58px;
        }

        .brand {
            color: #28577f;
            font-size: 17px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            color: #328c84;
            font-size: 9px;
            margin: 3px 0 0;
        }

        h1 {
            color: #28577f;
            font-size: 18px;
            margin: 0 0 12px;
        }

        h2 {
            color: #28577f;
            font-size: 12px;
            margin: 18px 0 8px;
        }

        .details {
            border-collapse: collapse;
            margin-bottom: 14px;
            width: 100%;
        }

        .details td {
            border-bottom: 1px solid #dce8ed;
            padding: 6px 8px;
            vertical-align: top;
            width: 50%;
        }

        .label {
            color: #778692;
            display: block;
            font-size: 8px;
            margin-bottom: 2px;
        }

        .evaluation {
            background: #f4fafb;
            border: 1px solid #dce8ed;
            margin-bottom: 8px;
            padding: 9px;
            white-space: pre-line;
        }

        .lesson-table {
            border-collapse: collapse;
            width: 100%;
        }

        .lesson-table th {
            background: #e8f3f5;
            color: #28577f;
            font-size: 8px;
            text-align: left;
        }

        .lesson-table th,
        .lesson-table td {
            border: 1px solid #dce8ed;
            padding: 6px;
            vertical-align: top;
        }

        .attendance-time {
            color: #778692;
            display: block;
            font-size: 8px;
            margin-top: 3px;
        }

        .signature {
            margin-top: 42px;
            page-break-inside: avoid;
            text-align: center;
            width: 220px;
        }

        .signature-space {
            height: 54px;
        }

        .signature-line {
            border-top: 1px solid #24445d;
            padding-top: 5px;
        }

        .empty {
            color: #778692;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 72px">
                <img class="logo" src="{{ $logoData }}" alt="Logo Effata">
            </td>
            <td>
                <p class="brand">Effata Fun Learning</p>
                <p class="subtitle">Laporan Perkembangan Pembelajaran</p>
            </td>
        </tr>
    </table>

    <h1>Report Pembelajaran</h1>
    <table class="details">
        <tr>
            <td><span class="label">Kelas</span>{{ $learningClass->name }}</td>
            <td><span class="label">Periode</span>{{ \Carbon\Carbon::createFromFormat('!Y-m', $report->period_start)->locale('id')->translatedFormat('F Y') }} — {{ \Carbon\Carbon::createFromFormat('!Y-m', $report->period_end)->locale('id')->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <td><span class="label">Guru</span>{{ $report->teacher->name }}</td>
            <td><span class="label">Murid</span>{{ $report->student->name }}</td>
        </tr>
        <tr>
            <td><span class="label">Program</span>{{ $report->program->value }}</td>
            <td><span class="label">Kategori</span>{{ $learningClass->category?->value ?? '—' }}</td>
        </tr>
    </table>

    <h2>Evaluasi Perkembangan</h2>
    <div class="evaluation">{{ $report->development_evaluation ?: 'Tidak ada evaluasi.' }}</div>

    <h2>Catatan Tambahan</h2>
    <div class="evaluation">{{ $report->additional_notes ?: 'Tidak ada catatan tambahan.' }}</div>

    <h2>Riwayat Les dan Absensi Pengajar</h2>
    <table class="lesson-table">
        <thead>
            <tr>
                <th style="width: 13%">Tanggal</th>
                <th style="width: 12%">Waktu</th>
                <th style="width: 20%">Pengajar</th>
                <th style="width: 20%">Absensi / Status</th>
                <th>Deskripsi Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lessons as $lesson)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($lesson->lesson_date)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ substr($lesson->lesson_time, 0, 5) }} <br>({{ $lesson->duration_minutes }} mnt)</td>
                    <td>{{ $lesson->teacher?->name ?? $report->teacher->name }}</td>
                    <td>
                        {{ $lesson->teacher_attendance_at ? 'Hadir' : ($lesson->lessonStatus?->system_key === 'dibatalkan' ? 'Tidak ada (dibatalkan)' : 'Belum tercatat') }}
                        @if ($lesson->lessonStatus)
                            <span class="attendance-time">{{ $lesson->lessonStatus->label }}</span>
                        @endif
                        @if ($lesson->teacher_attendance_at)
                            <span class="attendance-time">{{ \Illuminate\Support\Carbon::parse($lesson->teacher_attendance_at)->locale('id')->translatedFormat('d F Y, H:i') }}</span>
                        @endif
                    </td>
                    <td>{{ $lesson->lesson_description ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="5">Tidak ada jadwal les pada periode report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div>Mengetahui,</div>
        <div>Pengajar</div>
        <div class="signature-space"></div>
        <div class="signature-line">{{ $report->teacher->name }}</div>
    </div>
</body>
</html>
