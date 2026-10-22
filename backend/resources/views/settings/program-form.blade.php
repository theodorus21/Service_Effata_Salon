@extends('layouts.dashboard')

@php($isEditing = $program->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Program')
@section('breadcrumb', 'Pengaturan / Program')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">KONFIGURASI APLIKASI</p>
            <h1>{{ $isEditing ? 'Ubah Program' : 'Tambah Program' }}</h1>
            <p>Kelola pilihan program untuk kelas.</p>
        </div>
        @if (auth()->user()->hasPermission('settings.view'))
            <a class="secondary-action" href="{{ route('settings.index') }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('settings.programs.update', $program) : route('settings.programs.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="master-data-field">
                <label for="value">Nama program</label>
                <input id="value" name="value" type="text" value="{{ old('value', $program->value) }}" required maxlength="255">
                @error('value')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="sort_order">Urutan tampil</label>
                <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $program->sort_order ?? 0) }}" required min="0" max="65535" step="1">
                @error('sort_order')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('settings.view'))
                    <a class="secondary-action" href="{{ route('settings.index') }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan program' }}</button>
            </div>
        </form>
    </section>
@endsection
