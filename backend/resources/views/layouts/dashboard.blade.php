<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title') | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/dashboard.css', 'resources/js/dashboard.js'])
    </head>
    <body class="dashboard-page">
        @include('partials.dashboard-sidebar')
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <main class="dashboard-main">
            <header class="topbar">
                <button class="mobile-menu-button" id="mobile-menu-button" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
                </button>
                <div class="topbar-title">
                    <p>Effata Fun Learning <span>/</span> @yield('breadcrumb')</p>
                </div>
                <div class="topbar-actions">
                    <div class="topbar-divider"></div>
                    <div class="profile-button" aria-label="Profil Admin Effata">
                        <span class="avatar avatar-admin">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}</small></span>
                    </div>
                </div>
            </header>
            <div class="dashboard-content master-data-content">
                @yield('content')
                <footer class="dashboard-footer">© 2026 Effata Fun Learning <span>·</span> Dibuat dengan sepenuh hati</footer>
            </div>
        </main>
        @yield('scripts')
    </body>
</html>
