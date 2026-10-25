@extends('layouts.dashboard')

@section('title', 'Generate Jadwal Les')
@section('breadcrumb', 'Kelas / Generate Jadwal')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN / KELAS</p>
            <h1>Generate Jadwal Les</h1>
            <p>{{ $learningClass->name }} · {{ $learningClass->student->name }} · Guru {{ $learningClass->teacher->name }}</p>
        </div>
        <a class="secondary-action" href="{{ route('learning-classes.index') }}">Kembali ke daftar kelas</a>
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ route('learning-classes.lessons.generate', $learningClass) }}">
            @csrf
            <div class="lesson-period-form">
                <div>
                    <label for="generate-month">Bulan jadwal</label>
                    <select id="generate-month" name="month" required>
                        @foreach (range(1, 12) as $monthNumber)
                            <option value="{{ $monthNumber }}" @selected((int) old('month', $month) === $monthNumber)>
                                {{ \Carbon\Carbon::create()->month($monthNumber)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    @error('month')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="generate-year">Tahun jadwal</label>
                    <input id="generate-year" name="year" type="number" min="2000" max="2100" value="{{ old('year', $year) }}" required>
                    @error('year')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="master-data-help">Jadwal yang tanggalnya hari ini atau sebelumnya tidak akan dibuat. Jadwal yang sudah tercatat tidak akan diubah.</p>
            <div class="master-data-form-actions">
                <a class="secondary-action" href="{{ route('learning-classes.index') }}">Batal</a>
                <button class="primary-action" type="submit">Generate jadwal</button>
            </div>
        </form>
    </section>
@endsection
