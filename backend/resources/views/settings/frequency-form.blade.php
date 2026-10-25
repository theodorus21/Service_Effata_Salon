@extends('layouts.dashboard')

@php($isEditing = $classFrequency->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Frekuensi Kelas')
@section('breadcrumb', 'Pengaturan / Frekuensi Kelas')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">KONFIGURASI APLIKASI</p>
            <h1>{{ $isEditing ? 'Ubah Frekuensi Kelas' : 'Tambah Frekuensi Kelas' }}</h1>
            <p>Kelola pilihan frekuensi untuk produk kelas, misalnya "2x seminggu".</p>
        </div>
        @if (auth()->user()->hasPermission('settings.view'))
            <a class="secondary-action" href="{{ route('settings.index') }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('settings.class-frequencies.update', $classFrequency) : route('settings.class-frequencies.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="master-data-field">
                <label for="value">Nama frekuensi</label>
                <input id="value" name="value" type="text" value="{{ old('value', $classFrequency->value) }}" required maxlength="255">
                @error('value')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="sort_order">Urutan tampil</label>
                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $classFrequency->sort_order ?? 0) }}" required min="0" max="65535" step="1">
                @error('sort_order')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="is_monthly">
                    <input id="is_monthly" name="is_monthly" type="checkbox" value="1" @checked(old('is_monthly', $classFrequency->is_monthly))>
                    Frekuensi bulanan
                </label>
                <small>Jika dicentang, kelas dengan produk ini memakai jadwal mingguan yang di-generate per bulan. Jika tidak, kelas memakai pilihan tanggal dan jam.</small>
                @error('is_monthly')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('settings.view'))
                    <a class="secondary-action" href="{{ route('settings.index') }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan frekuensi' }}</button>
            </div>
        </form>
    </section>
@endsection
