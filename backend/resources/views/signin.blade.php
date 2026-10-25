<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/jpeg" href="{{ asset('images/effata-logo.jpg') }}" />
        <meta
            name="description"
            content="Masuk ke Effata Fun Learning dan lanjutkan perjalanan belajar yang menyenangkan."
        />
        <title>Masuk | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/signin.css', 'resources/js/signin.js'])
    </head>
    <body class="signin-page">
        <main class="login-layout">
            <section class="brand-panel" aria-label="Belajar dengan menyenangkan di Effata Fun Learning">
                <a class="brand" href="{{ route('signin') }}" aria-label="Effata Fun Learning, halaman login">
                    <img class="brand-logo" src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                    <span class="brand-name">Effata <span>Fun Learning</span></span>
                </a>

                <div class="brand-copy">
                    <p class="eyebrow">BELAJAR • BERTUMBUH • BERGEMBIRA</p>
                    <h1>Setiap hari adalah petualangan <em>baru!</em></h1>
                    <p class="brand-description">
                        Temukan serunya belajar, asah bakat, dan rayakan setiap
                        langkah kecil bersama Effata.
                    </p>
                </div>

                <div class="learning-art" aria-hidden="true">
                    <span class="learning-art-sparkle learning-art-sparkle-one">✦</span>
                    <span class="learning-art-sparkle learning-art-sparkle-two">✦</span>
                    <span class="learning-art-doodle learning-art-doodle-one">A</span>
                    <span class="learning-art-doodle learning-art-doodle-two">♪</span>
                    <svg viewBox="0 0 440 310" role="presentation">
                        <ellipse cx="220" cy="278" rx="160" ry="18" fill="#244D79" opacity=".12" />
                        <path d="M68 121c42-20 84-17 126 6v119c-42-23-84-26-126-6V121Z" fill="#fff8df" />
                        <path d="M194 127c42-23 84-26 126-6v119c-42-20-84-17-126 6V127Z" fill="#fff1c2" />
                        <path d="M194 127v119M82 143c31-11 62-9 92 4M82 165c31-11 62-9 92 4M82 187c31-11 62-9 92 4M216 147c30-13 61-15 92-4M216 169c30-13 61-15 92-4M216 191c30-13 61-15 92-4" fill="none" stroke="#f0b65c" stroke-linecap="round" stroke-width="5" />
                        <path d="M130 117c0-29 21-51 49-51 23 0 39 17 43 38 7-9 19-15 32-15 24 0 42 18 42 42v19c-56-18-111-12-166 16v-49Z" fill="#69c5bd" />
                        <circle cx="164" cy="93" r="5" fill="#244D79" />
                        <circle cx="220" cy="90" r="5" fill="#244D79" />
                        <path d="M176 108c9 9 19 9 28 0" fill="none" stroke="#244D79" stroke-linecap="round" stroke-width="4" />
                        <path d="M132 119c-17 2-31 16-33 35M286 125c17 6 27 20 27 36" fill="none" stroke="#e68e67" stroke-linecap="round" stroke-width="9" />
                    </svg>
                </div>

                <p class="brand-footer">Belajar jadi lebih seru kalau dilakukan bersama.</p>
            </section>

            <section class="form-panel" aria-labelledby="login-title">
                <div class="form-wrap">
                    <div class="mobile-brand" aria-hidden="true">
                        <img class="brand-logo" src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                        <span class="brand-name">Effata <span>Fun Learning</span></span>
                    </div>

                    <div class="form-heading">
                        <p class="eyebrow">YUK, LANJUTKAN PETUALANGANMU</p>
                        <h2 id="login-title">Halo, teman belajar!</h2>
                        <p>Masuk dulu, lalu kita belajar dan bertumbuh bersama.</p>
                    </div>

                    @if (session('status'))
                        <p class="form-message" role="status">{{ session('status') }}</p>
                    @endif

                    <form id="login-form" method="POST" action="{{ route('signin.store') }}">
                        @csrf
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3.5" y="5" width="17" height="14" rx="2"></rect>
                                <path d="m4.5 7 7.5 6 7.5-6"></path>
                            </svg>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                value="{{ old('email') }}"
                                required
                            />
                        </div>
                        @error('email')<p class="form-message form-message-error" role="alert">{{ $message }}</p>@enderror

                        <div class="password-label">
                            <label for="password">Kata sandi</label>
                            <a class="text-link" href="#lupa-kata-sandi">Lupa kata sandi?</a>
                        </div>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="4.5" y="10" width="15" height="11" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                <path d="M12 14.5v2"></path>
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            />
                            <button
                                class="password-toggle"
                                type="button"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"></path>
                                    <circle cx="12" cy="12" r="2.5"></circle>
                                </svg>
                            </button>
                        </div>

                        <label class="remember-option">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember')) />
                            <span class="checkmark" aria-hidden="true"></span>
                            <span>Ingat saya</span>
                        </label>

                        <button class="submit-button" type="submit">
                            Masuk
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>

                    <p class="signup-prompt">
                        Belum punya akun? <a class="text-link" href="{{ route('signup') }}">Daftar sekarang</a>
                    </p>
                    <p class="form-footer">© 2026 Effata Fun Learning. Seluruh hak cipta dilindungi.</p>
                </div>
            </section>
        </main>
    </body>
</html>
