<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/jpeg" href="{{ asset('images/effata-logo.jpg') }}" />
        <meta
            name="description"
            content="Buat akun Effata Fun Learning untuk mengelola reservasi dan layanan."
        />
        <meta name="api-users-endpoint" content="{{ url('/api/v1/users') }}" />
        <title>Daftar | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/signup.css', 'resources/js/signup.js'])
    </head>
    <body>
        <main class="login-layout">
            <section class="brand-panel" aria-label="Tentang Effata Fun Learning">
                <a class="brand" href="{{ route('signin') }}" aria-label="Effata Fun Learning, halaman login">
                    <img class="brand-logo" src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                    <span class="brand-name">Effata <span>Fun Learning</span></span>
                </a>

                <div class="brand-copy">
                    <p class="eyebrow">SALON &amp; BEAUTY CARE</p>
                    <h1>Mulai perjalanan <em>cantikmu.</em></h1>
                    <p class="brand-description">
                        Buat akun Effata Fun Learning dan nikmati pengalaman perawatan
                        yang terasa lebih personal.
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

            <section class="form-panel signup-panel" aria-labelledby="signup-title">
                <div class="form-wrap signup-wrap">
                    <a class="mobile-brand signup-mobile-brand" href="{{ route('signin') }}" aria-label="Effata Fun Learning, halaman login">
                        <img class="brand-logo" src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                        <span class="brand-name">Effata <span>Fun Learning</span></span>
                    </a>

                    <div class="form-heading">
                        <p class="eyebrow">GABUNG BERSAMA KAMI</p>
                        <h2 id="signup-title">Buat akun baru</h2>
                        <p>Isi detail berikut untuk memulai.</p>
                    </div>

                    <form id="signup-form" novalidate>
                        <div class="signup-field">
                            <label for="full-name">Nama lengkap</label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.5"></circle>
                                    <path d="M4.5 20a7.5 7.5 0 0 1 15 0"></path>
                                </svg>
                                <input
                                    id="full-name"
                                    name="name"
                                    type="text"
                                    placeholder="Nama lengkap kamu"
                                    autocomplete="name"
                                    required
                                />
                            </div>
                        </div>

                        <div class="signup-field">
                            <label for="signup-email">Email</label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3.5" y="5" width="17" height="14" rx="2"></rect>
                                    <path d="m4.5 7 7.5 6 7.5-6"></path>
                                </svg>
                                <input
                                    id="signup-email"
                                    name="email"
                                    type="email"
                                    placeholder="nama@email.com"
                                    autocomplete="email"
                                    required
                                />
                            </div>
                        </div>

                        <div class="signup-field">
                            <label for="signup-password">Kata sandi</label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4.5" y="10" width="15" height="11" rx="2"></rect>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    <path d="M12 14.5v2"></path>
                                </svg>
                                <input
                                    id="signup-password"
                                    name="password"
                                    type="password"
                                    placeholder="Minimal 8 karakter"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                />
                            </div>
                        </div>

                        <div class="signup-field">
                            <label for="password-confirmation">Konfirmasi kata sandi</label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4.5" y="10" width="15" height="11" rx="2"></rect>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    <path d="M12 14.5v2"></path>
                                </svg>
                                <input
                                    id="password-confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    placeholder="Ulangi kata sandi"
                                    autocomplete="new-password"
                                    required
                                />
                            </div>
                        </div>

                        <label class="remember-option terms-option">
                            <input id="terms" type="checkbox" name="terms" required />
                            <span class="checkmark" aria-hidden="true"></span>
                            <span>Saya menyetujui <a class="text-link" href="#syarat">Syarat &amp; Ketentuan</a> dan <a class="text-link" href="#privasi">Kebijakan Privasi</a></span>
                        </label>

                        <button class="submit-button" type="submit">
                            Buat akun
                            <span aria-hidden="true">→</span>
                        </button>
                        <p class="form-message" id="signup-message" role="status" aria-live="polite"></p>
                    </form>

                    <p class="signup-prompt">
                        Sudah punya akun? <a class="text-link" href="{{ route('signin') }}">Masuk</a>
                    </p>
                    <p class="form-footer">© 2026 Effata Fun Learning. Seluruh hak cipta dilindungi.</p>
                </div>
            </section>
        </main>
    </body>
</html>
