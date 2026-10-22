@extends('layouts.dashboard')

@section('title', 'Detail Guru')
@section('breadcrumb', 'Master Data / Guru / Detail')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">MASTER DATA / GURU</p>
            <h1>Detail Guru</h1>
            <p>Informasi lengkap profil guru.</p>
        </div>
        <div class="master-data-heading-actions">
            @if (auth()->user()->hasPermission('master_data.guru.view'))
                <a class="secondary-action" href="{{ route('master-data.index', ['role' => 'guru']) }}">Kembali</a>
            @endif
            @if (auth()->user()->hasPermission('master_data.guru.update'))
                <a class="primary-action" href="{{ route('master-data.edit', ['role' => 'guru', 'user' => $user->id]) }}">Ubah data</a>
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
            <div><dt>Alamat</dt><dd>{{ $user->address }}</dd></div>
            <div><dt>Bank rekening</dt><dd>{{ $user->bank_name }}</dd></div>
            <div><dt>Nomor rekening</dt><dd>{{ $user->bank_account_number }}</dd></div>
            <div class="guru-detail-quote"><dt>Quotes of Teaching</dt><dd>{{ $user->teaching_quote }}</dd></div>
        </dl>
    </section>

    <section class="guru-detail-images" aria-label="Dokumen guru">
        @foreach (['selfie_image' => 'Gambar selfie', 'ktp_image' => 'Gambar KTP'] as $imageField => $label)
            <article class="panel guru-document-card">
                <h2>{{ $label }}</h2>
                @if ($user->{$imageField} && auth()->user()->hasPermission('master_data.guru.images'))
                    <a href="{{ route('master-data.image', ['role' => 'guru', 'user' => $user->id, 'image' => $imageField]) }}" target="_blank" rel="noopener">
                        <img src="{{ route('master-data.image', ['role' => 'guru', 'user' => $user->id, 'image' => $imageField]) }}" alt="{{ $label }} {{ $user->name }}">
                    </a>
                @else
                    <p class="guru-document-empty">Belum ada gambar.</p>
                @endif
            </article>
        @endforeach
    </section>
@endsection
