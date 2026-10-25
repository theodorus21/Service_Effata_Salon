@extends('layouts.dashboard')

@section('title', 'Detail Admin')
@section('breadcrumb', 'Master Data / Admin / Detail')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">MASTER DATA / ADMIN</p>
            <h1>Detail Admin</h1>
            <p>Informasi lengkap profil admin.</p>
        </div>
        <div class="master-data-heading-actions">
            @if (auth()->user()->hasPermission('master_data.admin.view'))
                <a class="secondary-action" href="{{ route('master-data.index', ['role' => 'admin']) }}">Kembali</a>
            @endif
            @if (auth()->user()->hasPermission('master_data.admin.update'))
                <a class="primary-action" href="{{ route('master-data.edit', ['role' => 'admin', 'user' => $user->id]) }}">Ubah data</a>
            @endif
        </div>
    </section>

    <section class="panel guru-detail-panel">
        <div class="guru-detail-heading">
            <span class="avatar avatar-plum">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->nickname }}</p>
            </div>
        </div>

        <dl class="guru-detail-grid">
            <div><dt>Nama lengkap</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>Nama panggilan</dt><dd>{{ $user->nickname }}</dd></div>
            <div><dt>Email</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
            <div><dt>No. telepon</dt><dd><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a></dd></div>
            <div><dt>Status user</dt><dd>{{ $user->status === 'active' ? 'Active' : 'Inactive' }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $user->address }}</dd></div>
            <div><dt>Bank rekening</dt><dd>{{ $user->bank_name }}</dd></div>
            <div><dt>Nomor rekening</dt><dd>{{ $user->bank_account_number }}</dd></div>
        </dl>
    </section>

    <section class="guru-detail-images" aria-label="Dokumen admin">
        @foreach (['selfie_image' => 'Gambar selfie', 'ktp_image' => 'Gambar KTP'] as $imageField => $label)
            <article class="panel guru-document-card">
                <h2>{{ $label }}</h2>
                @if ($user->{$imageField} && auth()->user()->hasPermission('master_data.admin.images'))
                    <a href="{{ route('master-data.image', ['role' => 'admin', 'user' => $user->id, 'image' => $imageField]) }}" target="_blank" rel="noopener">
                        <img src="{{ route('master-data.image', ['role' => 'admin', 'user' => $user->id, 'image' => $imageField]) }}" alt="{{ $label }} {{ $user->name }}">
                    </a>
                @else
                    <p class="guru-document-empty">Belum ada gambar.</p>
                @endif
            </article>
        @endforeach
    </section>
@endsection
