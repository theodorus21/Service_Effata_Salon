@extends('layouts.dashboard')

@php($isEditing = $role->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Role')
@section('breadcrumb', 'Role Akses / '.($isEditing ? 'Ubah' : 'Tambah'))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN AKSES</p>
            <h1>{{ $isEditing ? 'Ubah Role' : 'Tambah Role' }}</h1>
            <p>{{ $isEditing ? 'Perbarui nama tampilan role.' : 'Tambahkan role baru ke daftar aplikasi.' }}</p>
        </div>
        @if (auth()->user()->hasPermission('role_access.view'))
            <a class="secondary-action" href="{{ route('role-access.index') }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('role-access.update', $role) : route('role-access.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="master-data-field">
                <label for="name">Nama role</label>
                <input id="name" name="name" type="text" value="{{ old('name', $role->name) }}" required maxlength="100" autocomplete="off">
                @error('name')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @if ($isEditing)
                <div class="master-data-field">
                    <label for="code">Kode role</label>
                    <input id="code" type="text" value="{{ $role->code }}" readonly>
                    <small>Kode role tidak dapat diubah agar data pengguna dan fungsi sistem tetap konsisten.</small>
                </div>
            @endif
            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission('role_access.view'))
                    <a class="secondary-action" href="{{ route('role-access.index') }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan role' }}</button>
            </div>
        </form>
    </section>
@endsection
