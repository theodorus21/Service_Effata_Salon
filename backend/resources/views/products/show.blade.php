@extends('layouts.dashboard')

@section('title', 'Detail Produk')
@section('breadcrumb', 'Produk / Detail')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN PEMBELAJARAN</p>
            <h1>Detail Produk</h1>
            <p>Informasi lengkap produk kelas.</p>
        </div>
        <div class="master-data-heading-actions">
            <a class="secondary-action" href="{{ route('products.index') }}">Kembali</a>
            @if (auth()->user()->hasPermission('products.update'))
                <a class="primary-action" href="{{ route('products.edit', $product) }}">Ubah produk</a>
            @endif
        </div>
    </section>

    <section class="panel guru-detail-panel">
        <div class="guru-detail-heading">
            <span class="avatar avatar-plum">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
            <div>
                <h2>{{ $product->name }}</h2>
                <p>{{ $product->code }} · {{ ucfirst($product->status) }}</p>
            </div>
        </div>

        <dl class="guru-detail-grid">
            <div><dt>Kode produk</dt><dd>{{ $product->code }}</dd></div>
            <div><dt>Nama kelas</dt><dd>{{ $product->name }}</dd></div>
            <div><dt>Level</dt><dd>{{ $product->level->value }}</dd></div>
            <div><dt>Program</dt><dd>{{ $product->program->value }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $product->category->value }}</dd></div>
            <div><dt>Frekuensi</dt><dd>{{ $product->frequency?->value ?? '-' }}</dd></div>
            <div><dt>Durasi</dt><dd>{{ $product->duration_minutes }} menit</dd></div>
            <div><dt>Harga</dt><dd>Rp {{ number_format($product->price, 0, ',', '.') }}</dd></div>
            <div><dt>Status</dt><dd>{{ ucfirst($product->status) }}</dd></div>
            <div><dt>Dibuat oleh</dt><dd>{{ $product->creator?->name ?? '-' }}</dd></div>
            <div><dt>Diubah oleh</dt><dd>{{ $product->updater?->name ?? '-' }}</dd></div>
            <div><dt>Deskripsi</dt><dd>{{ $product->description ?: '-' }}</dd></div>
        </dl>
    </section>
@endsection
