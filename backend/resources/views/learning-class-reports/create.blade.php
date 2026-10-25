@extends('layouts.dashboard')

@section('title', 'Tambah Report Kelas')
@section('breadcrumb', 'Kelas / Report / Tambah')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / REPORT</p>
            <h1>Tambah Report</h1>
            <p>Periksa data kelas untuk periode yang dipilih.</p>
        </div>
        <a class="secondary-action" href="{{ route('learning-classes.reports.index', ['learningClass' => $learningClass, ...$period]) }}">Kembali</a>
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ route('learning-classes.reports.store', ['learningClass' => $learningClass, ...$period]) }}">
            @csrf

            <div class="master-data-field">
                <label>Guru yang mengajar</label>
                <input type="text" value="{{ $learningClass->teacher->name }}" readonly>
            </div>
            <div class="master-data-field">
                <label>Murid</label>
                <input type="text" value="{{ $learningClass->student->name }}" readonly>
            </div>
            <div class="master-data-field">
                <label>Program</label>
                <input type="text" value="{{ $learningClass->program->value }}" readonly>
            </div>
            <div class="master-data-field">
                <label for="period_start">Periode mulai</label>
                <input id="period_start" name="period_start" type="month" value="{{ old('period_start', $period['period_start']) }}" required>
                @error('period_start')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="period_end">Periode sampai</label>
                <input id="period_end" name="period_end" type="month" value="{{ old('period_end', $period['period_end']) }}" required>
                <small>Periode selesai harus sama dengan atau setelah periode mulai.</small>
                @error('period_end')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="development_evaluation">Evaluasi perkembangan</label>
                <textarea id="development_evaluation" name="development_evaluation" maxlength="10000">{{ old('development_evaluation') }}</textarea>
                @error('development_evaluation')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="additional_notes">Catatan tambahan</label>
                <textarea id="additional_notes" name="additional_notes" maxlength="10000">{{ old('additional_notes') }}</textarea>
                @error('additional_notes')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>

            <div class="master-data-heading-actions">
                <button class="primary-action" type="submit">Simpan Report</button>
                <a class="secondary-action" href="{{ route('learning-classes.reports.index', ['learningClass' => $learningClass, ...$period]) }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
