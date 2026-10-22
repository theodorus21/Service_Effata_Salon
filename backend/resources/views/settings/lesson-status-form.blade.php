@extends('layouts.dashboard')

@php($isEditing = $lessonStatus->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Status Jadwal Les')
@section('breadcrumb', 'Pengaturan / Status Jadwal Les')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">KONFIGURASI APLIKASI</p>
            <h1>{{ $isEditing ? 'Ubah Status Jadwal Les' : 'Tambah Status Jadwal Les' }}</h1>
            <p>Atur nama dan warna status untuk record jadwal les.</p>
        </div>
        @if (auth()->user()->hasPermission('settings.view'))
            <a class="secondary-action" href="{{ route('settings.index') }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('settings.lesson-statuses.update', $lessonStatus) : route('settings.lesson-statuses.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="master-data-field">
                <label for="label">Nama status</label>
                <input id="label" name="label" type="text" value="{{ old('label', $lessonStatus->label) }}" required maxlength="100">
                @error('label')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="color">Warna status</label>
                <input id="color" name="color" type="color" value="{{ old('color', $lessonStatus->color ?? '#dc3545') }}" required>
                @error('color')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="sort_order">Urutan tampil</label>
                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $lessonStatus->sort_order ?? 0) }}" required min="0" max="65535" step="1">
                @error('sort_order')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('settings.view'))
                    <a class="secondary-action" href="{{ route('settings.index') }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan status' }}</button>
            </div>
        </form>
    </section>
@endsection
