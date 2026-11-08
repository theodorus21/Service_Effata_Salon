<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="Effata Fun Learning adalah ruang belajar yang menyenangkan untuk membantu anak bertumbuh, berkreasi, dan menemukan hal baru." />
        <link rel="icon" type="image/jpeg" href="{{ asset('images/effata-logo.jpg') }}" />
        <title>Effata Fun Learning | Belajar Seru, Tumbuh Bersama</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/landing.css'])
    </head>
    <body class="landing-page">
        @php($user = auth()->guard('member')->user())
        <header class="landing-header">
            <nav class="landing-nav" aria-label="Navigasi utama">
                <a class="landing-brand" href="{{ route('home') }}" aria-label="Effata Fun Learning, beranda">
                    <img src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                    <span>Effata <strong>Fun Learning</strong></span>
                </a>
                <div class="landing-nav-links">
                    <a href="#program">Program</a>
                    <a href="#tentang">Tentang Effata</a>
                </div>
                <div class="landing-nav-actions">
                    @if ($user?->role === \App\Models\User::ROLE_STUDENT && $user->status === 'active')
                        <a class="landing-member-link landing-member-name" href="{{ route('member.home') }}">{{ $user->name }}</a>
                    @else
                        <a class="landing-member-link" href="{{ route('member.home') }}">Member</a>
                        <a class="landing-button landing-button-small" href="{{ route('signup') }}">Daftar</a>
                    @endif
                </div>
            </nav>
        </header>

        <main>
            <section class="landing-hero" id="tentang">
                <div class="landing-container hero-grid">
                    <div class="hero-copy">
                        <p class="landing-eyebrow"><span></span> RUANG BELAJAR PENUH CERITA</p>
                        <h1>Belajar seru,<br />tumbuh <em>bersama.</em></h1>
                        <p class="hero-description">
                            Setiap anak punya cara istimewa untuk berkembang. Mari temukan
                            hal baru, asah rasa ingin tahu, dan nikmati proses belajar di Effata.
                        </p>
                        <div class="hero-actions">
                            <a class="landing-button" href="{{ route('signup') }}">
                                Mulai perjalananmu <span aria-hidden="true">→</span>
                            </a>
                            <a class="landing-text-link" href="#program">Lihat program <span aria-hidden="true">↓</span></a>
                        </div>
                        <div class="hero-caption">
                            <span class="caption-icon" aria-hidden="true">✦</span>
                            <p>Belajar dengan gembira.<br /><strong>Bertumbuh dengan percaya diri.</strong></p>
                        </div>
                    </div>

                    <div class="hero-visual" aria-hidden="true">
                        <div class="visual-orbit orbit-one"></div>
                        <div class="visual-orbit orbit-two"></div>
                        <div class="visual-sparkle sparkle-one">✦</div>
                        <div class="visual-sparkle sparkle-two">✧</div>
                        <div class="visual-note">♫</div>
                        <div class="visual-tag tag-top"><span>A</span> Yuk, belajar!</div>
                        <svg class="learning-illustration" viewBox="0 0 560 440" role="presentation">
                            <ellipse cx="282" cy="382" rx="190" ry="22" fill="#204b63" opacity=".1" />
                            <circle cx="280" cy="210" r="152" fill="#d9f0e9" />
                            <path d="M119 307c34-43 74-65 121-65s87 22 121 65v53H119v-53Z" fill="#f3bd62" />
                            <path d="M169 305c0-46 36-83 80-83s80 37 80 83v48H169v-48Z" fill="#4388b5" />
                            <path d="M195 279c14-19 33-29 54-29s40 10 54 29v74H195v-74Z" fill="#fffdf3" />
                            <path d="M249 250v103" stroke="#e1d8c5" stroke-width="4" />
                            <path d="M207 280h30m-30 13h30m24-13h26m-26 13h26" stroke="#8abdb4" stroke-linecap="round" stroke-width="4" />
                            <path d="M216 190c0-38 27-66 63-66 31 0 55 25 55 57v28H216v-19Z" fill="#e69a72" />
                            <path d="M220 183c4-37 25-57 60-57 24 0 44 14 51 35-18-8-34-9-49-2-22 10-39 17-62 24Z" fill="#36536a" />
                            <circle cx="247" cy="190" r="4" fill="#24445d" />
                            <circle cx="286" cy="190" r="4" fill="#24445d" />
                            <path d="M254 208c8 7 17 7 25 0" fill="none" stroke="#a75450" stroke-linecap="round" stroke-width="3" />
                            <path d="M220 270c-22-5-38-18-46-38m155 39c23-7 36-22 42-43" fill="none" stroke="#e69a72" stroke-linecap="round" stroke-width="13" />
                            <rect x="101" y="185" width="54" height="62" rx="12" transform="rotate(-12 101 185)" fill="#f5d875" />
                            <path d="m120 213 8 8 16-18" fill="none" stroke="#43877e" stroke-linecap="round" stroke-linejoin="round" stroke-width="5" />
                            <rect x="384" y="250" width="56" height="68" rx="12" transform="rotate(10 384 250)" fill="#f3b4a1" />
                            <path d="M401 272h22m-22 12h22m-22 12h14" stroke="#fff9f3" stroke-linecap="round" stroke-width="4" />
                        </svg>
                        <div class="visual-tag tag-bottom"><span>♫</span> Ide hebat dimulai dari rasa ingin tahu</div>
                    </div>
                </div>
                <div class="hero-bottom-shape" aria-hidden="true"></div>
            </section>

            <section class="program-section" id="program">
                <div class="landing-container">
                    <div class="section-heading">
                        <p class="landing-eyebrow"><span></span> TEMUKAN KEGEMARANMU</p>
                        <h2>Ruang untuk belajar<br />dan <em>berekspresi.</em></h2>
                        <p>Mulai dari hal yang disukai, lalu biarkan rasa ingin tahu membawa ke banyak kemungkinan.</p>
                    </div>
                    <div class="program-grid">
                        <article class="program-card program-card-study">
                            <div class="program-icon" aria-hidden="true">
                                <svg viewBox="0 0 48 48">
                                    <path d="M7 12c7-3 13-2 17 2v25c-4-4-10-5-17-2V12Zm34 0c-7-3-13-2-17 2v25c4-4 10-5 17-2V12Z" />
                                    <path d="M13 18c3-1 6 0 8 1m-8 5c3-1 6 0 8 1m7-7c2-1 5-2 8-1m-8 7c2-1 5-2 8-1" />
                                </svg>
                            </div>
                            <p class="card-number">01 / PELAJARAN</p>
                            <h3>Belajar dengan cara yang menyenangkan</h3>
                            <p>Kenali berbagai topik dan bangun pemahaman baru lewat pengalaman belajar yang terasa dekat.</p>
                            <a href="{{ route('signup') }}" aria-label="Daftar untuk program Pelajaran">Jelajahi bersama <span aria-hidden="true">→</span></a>
                        </article>
                        <article class="program-card program-card-music">
                            <div class="program-icon" aria-hidden="true">
                                <svg viewBox="0 0 48 48">
                                    <path d="M29 9v25.5a7 7 0 1 1-4-6.3V15l17-4v18.5a7 7 0 1 1-4-6.3V7l-9 2Z" />
                                    <path d="M8 16h8m-8 8h6m-6 8h5" />
                                </svg>
                            </div>
                            <p class="card-number">02 / MUSIK</p>
                            <h3>Temukan nada dan irama versimu</h3>
                            <p>Jadikan musik ruang untuk berekspresi, berlatih, dan menikmati setiap proses kreatif.</p>
                            <a href="{{ route('signup') }}" aria-label="Daftar untuk program Musik">Jelajahi bersama <span aria-hidden="true">→</span></a>
                        </article>
                    </div>
                </div>
            </section>

            <section class="landing-cta">
                <div class="landing-container cta-inner">
                    <div>
                        <p class="landing-eyebrow"><span></span> LANGKAH KECIL, CERITA BESAR</p>
                        <h2>Siap memulai petualangan<br />belajar yang baru?</h2>
                    </div>
                    <a class="landing-button landing-button-light" href="{{ route('signup') }}">
                        Buat akun sekarang <span aria-hidden="true">→</span>
                    </a>
                    <span class="cta-sparkle sparkle-left" aria-hidden="true">✦</span>
                    <span class="cta-sparkle sparkle-right" aria-hidden="true">✧</span>
                </div>
            </section>
        </main>

        <footer class="landing-footer">
            <div class="landing-container footer-inner">
                <a class="landing-brand" href="{{ route('home') }}">
                    <img src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                    <span>Effata <strong>Fun Learning</strong></span>
                </a>
                <p>Belajar jadi lebih seru kalau dilakukan bersama.</p>
                <span>© 2026 Effata Fun Learning</span>
            </div>
        </footer>
    </body>
</html>
