<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta
            name="description"
            content="Masuk ke Effata Fun Learning untuk mengelola layanan dan reservasi."
        />
        <title>Masuk | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/js/signin.js'])
    </head>
    <body>
        <main class="login-layout">
            <section class="brand-panel" aria-label="Tentang Effata Fun Learning">
                <a class="brand" href="{{ route('signin') }}" aria-label="Effata Fun Learning, halaman login">
                    <span class="brand-mark" aria-hidden="true">E</span>
                    <span class="brand-name">Effata <span>Fun Learning</span></span>
                </a>

                <div class="brand-copy">
                    <p class="eyebrow">SALON &amp; BEAUTY CARE</p>
                    <h1>Waktunya merasa lebih <em>istimewa.</em></h1>
                    <p class="brand-description">
                        Perawatan terbaik dimulai dari langkah kecil. Masuk dan
                        temukan kembali waktu untuk dirimu.
                    </p>
                </div>

                <div class="decorative-art" aria-hidden="true">
                    <div class="art-orbit art-orbit-outer"></div>
                    <div class="art-orbit art-orbit-inner"></div>
                    <div class="art-sun"></div>
                    <div class="art-leaf art-leaf-one"></div>
                    <div class="art-leaf art-leaf-two"></div>
                    <span class="art-sparkle sparkle-one">✳</span>
                    <span class="art-sparkle sparkle-two">✳</span>
                </div>

                <p class="brand-footer">Cantik, dengan caramu sendiri.</p>
            </section>

            <section class="form-panel" aria-labelledby="login-title">
                <div class="form-wrap">
                    <div class="mobile-brand" aria-hidden="true">
                        <span class="brand-mark">E</span>
                        <span class="brand-name">Effata <span>Fun Learning</span></span>
                    </div>

                    <div class="form-heading">
                        <p class="eyebrow">SELAMAT DATANG KEMBALI</p>
                        <h2 id="login-title">Masuk ke akunmu</h2>
                        <p>Masukkan detail akun untuk melanjutkan.</p>
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
