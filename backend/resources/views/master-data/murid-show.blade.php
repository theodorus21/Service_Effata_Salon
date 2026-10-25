@extends('layouts.dashboard')

@section('title', 'Detail Murid')
@section('breadcrumb', 'Master Data / Murid / Detail')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">MASTER DATA / MURID</p>
            <h1>Detail Murid</h1>
            <p>Informasi lengkap profil anak dan orang tua.</p>
        </div>
        <div class="master-data-heading-actions">
            @if (auth()->user()->hasPermission('master_data.murid.view'))
                <a class="secondary-action" href="{{ route('master-data.index', ['role' => 'murid']) }}">Kembali</a>
            @endif
            @if (auth()->user()->hasPermission('master_data.murid.update'))
                <a class="primary-action" href="{{ route('master-data.edit', ['role' => 'murid', 'user' => $user->id]) }}">Ubah data</a>
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
            <div><dt>Nama lengkap anak</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>Nama panggilan anak</dt><dd>{{ $user->nickname }}</dd></div>
            <div><dt>Nama orang tua</dt><dd>{{ $user->parent_name }}</dd></div>
            <div><dt>No. telepon orang tua</dt><dd><a href="tel:{{ $user->parent_phone }}">{{ $user->parent_phone }}</a></dd></div>
            <div><dt>Penjemput anak</dt><dd>{{ $user->child_pickup_person }}</dd></div>
            <div><dt>Email orang tua</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
            <div><dt>Nama kontak darurat</dt><dd>{{ $user->emergency_contact_name }}</dd></div>
            <div><dt>No. telepon kontak darurat</dt><dd><a href="tel:{{ $user->emergency_contact_phone }}">{{ $user->emergency_contact_phone }}</a></dd></div>
            <div><dt>Status user</dt><dd>{{ $user->status === 'active' ? 'Active' : 'Inactive' }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $user->address }}</dd></div>
            <div><dt>Karakteristik anak</dt><dd>{{ $user->child_characteristics }}</dd></div>
            <div><dt>Hobi anak</dt><dd>{{ $user->child_hobbies }}</dd></div>
            <div class="guru-detail-quote"><dt>Referensi</dt><dd>{{ $user->reference }}</dd></div>
        </dl>
    </section>

    <section class="guru-detail-images" aria-label="Foto anak">
        <article class="panel guru-document-card">
            <h2>Gambar foto anak</h2>
            @if ($user->child_photo && auth()->user()->hasPermission('master_data.murid.images'))
                <a href="{{ route('master-data.image', ['role' => 'murid', 'user' => $user->id, 'image' => 'child_photo']) }}" target="_blank" rel="noopener">
                    <img src="{{ route('master-data.image', ['role' => 'murid', 'user' => $user->id, 'image' => 'child_photo']) }}" alt="Foto {{ $user->name }}">
                </a>
            @else
                <p class="guru-document-empty">Belum ada gambar.</p>
            @endif
        </article>
    </section>
@endsection
