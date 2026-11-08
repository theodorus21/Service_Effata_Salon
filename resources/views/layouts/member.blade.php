<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="Portal member murid Effata Fun Learning." />
        <link rel="icon" type="image/jpeg" href="{{ asset('images/effata-logo.jpg') }}" />
        <title>@yield('title') | Effata Member</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/member.css'])
    </head>
    <body class="member-app">
        <header class="member-header">
            <div class="member-header-inner">
                <a class="member-brand" href="{{ route('member.home') }}">
                    <img src="{{ asset('images/effata-logo.jpg') }}" alt="" />
                    <span>Effata <strong>Member</strong></span>
                </a>
                <nav class="member-nav" aria-label="Navigasi member">
                    <a class="{{ request()->routeIs('member.profile*') ? 'active' : '' }}" href="{{ route('member.profile') }}">
                        <span class="member-nav-icon" aria-hidden="true">⌂</span>
                        Profil
                    </a>
                    <form method="POST" action="{{ route('member.logout') }}">
                        @csrf
                        <button class="member-logout" type="submit">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="member-main">
            @if (session('status'))
                <p class="member-flash" role="status">{{ session('status') }}</p>
            @endif
            @yield('content')
        </main>

        <footer class="member-footer">
            <span>Effata Fun Learning</span>
            <span>Belajar, bertumbuh, bergembira.</span>
        </footer>
    </body>
</html>
