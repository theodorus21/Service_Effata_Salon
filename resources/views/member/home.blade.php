@extends('layouts.member')

@section('title', 'Beranda')

@section('content')
    <section class="member-welcome">
        <div class="member-welcome-copy">
            <p class="member-eyebrow"><span></span> RUANG BELAJAR PRIBADIMU</p>
            <h1>Halo, {{ $user->nickname ?: $user->name }}!</h1>
            <p>Senang melihatmu kembali. Siap melanjutkan cerita belajarmu bersama Effata?</p>
        </div>
        <div class="member-welcome-art" aria-hidden="true">
            <span class="member-art-sparkle">✦</span>
            <svg viewBox="0 0 170 150" role="presentation">
                <path d="M25 92c20-17 42-17 61 0v34c-19-17-41-17-61 0V92Zm61 0c19-17 41-17 61 0v34c-20-17-42-17-61 0V92Z" fill="#fffdf3" />
                <path d="M86 92v34M38 103c11-5 23-5 35 0m-35 11c11-5 23-5 35 0m26-11c12-5 24-5 35 0m-35 11c12-5 24-5 35 0" fill="none" stroke="#77b8ae" stroke-linecap="round" stroke-width="4" />
                <path d="M55 77c0-18 13-31 31-31s31 13 31 31v10H55V77Z" fill="#e69a72" />
                <path d="M58 70c2-17 13-26 29-26 13 0 23 7 28 19-12-5-21-5-30 0-8 4-16 6-27 7Z" fill="#36536a" />
                <circle cx="74" cy="72" r="2.5" fill="#24445d" />
                <circle cx="96" cy="72" r="2.5" fill="#24445d" />
                <path d="M78 83c4 4 9 4 13 0" fill="none" stroke="#a75450" stroke-linecap="round" stroke-width="2.5" />
                <path d="m31 55 5 10 11 2-8 7 2 11-10-6-10 6 2-11-8-7 11-2 5-10Z" fill="#ffcf62" />
            </svg>
        </div>
        <div class="member-welcome-stamp"><span>✦</span> Senang belajar<br />bersama kamu</div>
    </section>

    <section class="member-menu-section" aria-labelledby="member-menu-title">
        <div class="member-section-heading">
            <div>
                <p class="member-eyebrow">RUANG MEMBER</p>
                <h2 id="member-menu-title">Menu untukmu</h2>
            </div>
            <span class="member-menu-count">1 menu</span>
        </div>

        <a class="member-menu-card" href="{{ route('member.profile') }}">
            <span class="member-menu-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48">
                    <circle cx="24" cy="16" r="8"></circle>
                    <path d="M9 41c0-8.3 6.7-15 15-15s15 6.7 15 15"></path>
                </svg>
            </span>
            <span class="member-menu-copy">
                <strong>Profil Saya</strong>
                <span>Lihat informasi dirimu dan atur kata sandi akun.</span>
            </span>
            <span class="member-menu-arrow" aria-hidden="true">→</span>
        </a>
    </section>

    <aside class="member-encouragement">
        <span aria-hidden="true">✦</span>
        <p>Setiap hari adalah kesempatan untuk menemukan hal baru.</p>
    </aside>
@endsection
