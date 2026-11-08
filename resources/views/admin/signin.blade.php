<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="Login khusus guru dan administrator Effata Fun Learning." />
        <link rel="icon" type="image/jpeg" href="{{ asset('images/effata-logo.jpg') }}" />
        <title>Login Pengelola | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/admin-signin.css'])
    </head>
    <body class="admin-signin-page">
        <main class="admin-login-shell">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                <span>Effata <strong>Fun Learning</strong></span>
            </a>

            <section class="admin-login-card" aria-labelledby="admin-login-title">
                <p class="admin-login-eyebrow">AREA PENGELOLA</p>
                <h1 id="admin-login-title">Masuk ke dashboard</h1>
                <p class="admin-login-description">Gunakan akun guru atau admin Effata.</p>

                @if (session('status'))
                    <p class="admin-login-status" role="status">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('admin.signin.store') }}">
                    @csrf
                    <div class="admin-login-field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            placeholder="nama@email.com"
                            required
                            autofocus
                        />
                        @error('email')<p class="admin-login-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="admin-login-field">
                        <label for="password">Kata sandi</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            required
                        />
                    </div>
                    <label class="admin-remember-option">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember')) />
                        <span>Ingat saya</span>
                    </label>
                    <button class="admin-login-submit" type="submit">Masuk <span aria-hidden="true">→</span></button>
                </form>

                <p class="admin-login-footer">Akses khusus untuk pengelola Effata.</p>
            </section>
        </main>
    </body>
</html>
