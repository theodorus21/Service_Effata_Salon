@extends('layouts.dashboard')

@php($isEditing = $product->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' Produk')
@section('breadcrumb', 'Produk / '.($isEditing ? 'Ubah' : 'Tambah'))

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>{{ $isEditing ? 'Ubah Produk' : 'Tambah Produk' }}</h1>
            <p>Kode produk dibuat otomatis dari program, kategori, frekuensi, dan level.</p>
        </div>
        <a class="secondary-action" href="{{ route('products.index') }}">Kembali</a>
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" action="{{ $isEditing ? route('products.update', $product) : route('products.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="master-data-field">
                <label for="name">Nama kelas</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required maxlength="255">
                @error('name')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @if ($isEditing)
                <div class="master-data-field">
                    <label for="code">Kode produk</label>
                    <input id="code" type="text" value="{{ $product->code }}" readonly>
                </div>
            @endif
            @foreach ([
                'level_id' => ['Level kelas', $levels],
                'program_id' => ['Program kelas', $programs],
                'category_id' => ['Kategori kelas', $categories],
                'frequency_id' => ['Frekuensi kelas', $frequencies],
            ] as $field => [$label, $options])
                <div class="master-data-field">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" required>
                        <option value="">Pilih {{ strtolower($label) }}</option>
                        @foreach ($options as $option)
                            <option value="{{ $option->id }}" @selected((string) old($field, $product->{$field}) === (string) $option->id)>{{ $option->value }}</option>
                        @endforeach
                    </select>
                    @error($field)<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div class="master-data-field">
                <label for="duration_minutes">Durasi (menit)</label>
                <input id="duration_minutes" name="duration_minutes" type="number" value="{{ old('duration_minutes', $product->duration_minutes) }}" required min="1" max="65535" step="1">
                @error('duration_minutes')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="price">Harga (Rp)</label>
                <input id="price" name="price" type="number" value="{{ old('price', $product->price) }}" required min="0" step="1">
                @error('price')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            <div class="master-data-field">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" maxlength="5000">{{ old('description', $product->description) }}</textarea>
                @error('description')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @if ($isEditing)
            <div class="master-data-field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $product->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @endif
            <div class="master-data-form-actions">
                <a class="secondary-action" href="{{ route('products.index') }}">Batal</a>
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan produk' }}</button>
            </div>
        </form>
    </section>
@endsection
