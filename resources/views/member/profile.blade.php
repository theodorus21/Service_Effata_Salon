@extends('layouts.member')

@section('title', 'Profil Saya')

@section('content')
    <div class="member-page-heading">
        <a class="member-back-link" href="{{ route('member.home') }}"><span aria-hidden="true">←</span> Beranda member</a>
        <p class="member-eyebrow">INFORMASI AKUN</p>
        <h1>Profil Saya</h1>
        <p>Informasi murid yang terhubung dengan akun member ini.</p>
    </div>

    <section class="member-profile-card" aria-label="Profil {{ $user->name }}">
        <div class="member-profile-summary">
            @if ($user->child_photo)
                <img class="member-avatar" src="{{ route('member.profile.photo') }}" alt="Foto {{ $user->name }}" />
            @else
                <span class="member-avatar member-avatar-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif
            <div class="member-profile-identity">
                <p class="member-eyebrow">MURID EFFATA</p>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->nickname ?: 'Nama panggilan belum diisi' }}</p>
            </div>
            <span class="member-status"><span></span> Akun aktif</span>
        </div>

        <div class="member-profile-groups">
            <section class="member-info-group">
                <div class="member-info-heading">
                    <span aria-hidden="true">01</span>
                    <h3>Data murid</h3>
                </div>
                <dl class="member-detail-grid">
                    <div><dt>Nama lengkap</dt><dd>{{ $user->name }}</dd></div>
                    <div><dt>Nama panggilan</dt><dd>{{ $user->nickname ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Tanggal lahir</dt><dd>{{ $user->birth_date?->format('d/m/Y') ?? 'Belum diisi' }}</dd></div>
                    <div><dt>Email akun</dt><dd>{{ $user->email }}</dd></div>
                    <div class="member-detail-wide"><dt>Alamat</dt><dd>{{ $user->address ?: 'Belum diisi' }}</dd></div>
                </dl>
            </section>

            <section class="member-info-group">
                <div class="member-info-heading">
                    <span aria-hidden="true">02</span>
                    <h3>Orang tua &amp; kontak</h3>
                </div>
                <dl class="member-detail-grid">
                    <div><dt>Nama orang tua</dt><dd>{{ $user->parent_name ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Telepon orang tua</dt><dd>{{ $user->parent_phone ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Penjemput</dt><dd>{{ $user->child_pickup_person ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Kontak darurat</dt><dd>{{ $user->emergency_contact_name ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Telepon darurat</dt><dd>{{ $user->emergency_contact_phone ?: 'Belum diisi' }}</dd></div>
                </dl>
            </section>

            <section class="member-info-group">
                <div class="member-info-heading">
                    <span aria-hidden="true">03</span>
                    <h3>Tentang saya</h3>
                </div>
                <dl class="member-detail-grid">
                    <div class="member-detail-wide"><dt>Karakteristik</dt><dd>{{ $user->child_characteristics ?: 'Belum diisi' }}</dd></div>
                    <div class="member-detail-wide"><dt>Hobi</dt><dd>{{ $user->child_hobbies ?: 'Belum diisi' }}</dd></div>
                </dl>
            </section>
        </div>
    </section>

    <section class="member-password-card" aria-labelledby="member-password-title">
        <div class="member-password-heading">
            <span class="member-password-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"></path>
                </svg>
            </span>
            <div>
                <p class="member-eyebrow">KEAMANAN AKUN</p>
                <h2 id="member-password-title">Ubah kata sandi</h2>
                <p>Gunakan kata sandi baru yang mudah kamu ingat dan tidak dibagikan ke orang lain.</p>
            </div>
        </div>

        <form class="member-password-form" method="POST" action="{{ route('member.profile.password') }}">
            @csrf
            @method('PUT')

            <div class="member-form-field">
                <label for="current_password">Kata sandi saat ini</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required />
                @error('current_password')<p class="member-field-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="member-form-field">
                <label for="new_password">Kata sandi baru</label>
                <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8" required />
                @error('new_password')<p class="member-field-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="member-form-field">
                <label for="new_password_confirmation">Ulangi kata sandi baru</label>
                <input id="new_password_confirmation" name="new_password_confirmation" type="password" autocomplete="new-password" minlength="8" required />
            </div>
            <button class="member-submit-button" type="submit">Simpan kata sandi <span aria-hidden="true">→</span></button>
        </form>
    </section>
@endsection
