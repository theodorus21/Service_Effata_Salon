<aside class="sidebar" id="sidebar">
    @if (auth()->user()->hasPermission('dashboard.view'))
    <a class="dashboard-brand" href="{{ route('dashboard') }}" aria-label="Effata Fun Learning, dashboard">
    @else
    <div class="dashboard-brand" aria-label="Effata Fun Learning">
    @endif
        <span class="brand-mark">E</span>
        <span class="brand-name">Effata <span>Fun Learning</span></span>
    @if (auth()->user()->hasPermission('dashboard.view'))
    </a>
    @else
    </div>
    @endif

    <p class="sidebar-label">MENU UTAMA</p>
    <nav class="sidebar-nav" aria-label="Menu utama">
        @if (auth()->user()->hasPermission('dashboard.view'))
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect>
                <rect x="13.5" y="3.5" width="7" height="5" rx="1.5"></rect>
                <rect x="13.5" y="11.5" width="7" height="9" rx="1.5"></rect>
                <rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('learning_classes.view'))
        <a class="nav-item {{ request()->routeIs('learning-classes.*') ? 'active' : '' }}" href="{{ route('learning-classes.index') }}" @if (request()->routeIs('learning-classes.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                <path d="M8 2.5v3M16 2.5v3M3 9h18M7 13h4M7 16h7"></path>
            </svg>
            <span>Kelas</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('class_schedules.view'))
        <a class="nav-item {{ request()->routeIs('class-schedules.*') ? 'active' : '' }}" href="{{ route('class-schedules.index') }}" @if (request()->routeIs('class-schedules.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3.5" y="5" width="17" height="15.5" rx="2"></rect>
                <path d="M7.5 3v4M16.5 3v4M3.5 9.5h17M8 13h.01M12 13h.01M16 13h.01M8 16.5h.01M12 16.5h.01"></path>
            </svg>
            <span>Jadwal Kelas</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('dashboard.menu.appointments'))
        <a class="nav-item" href="#appointments">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3.5" y="5.5" width="17" height="15" rx="2"></rect>
                <path d="M7.5 3.5v4M16.5 3.5v4M3.5 10h17M8 14h3M8 17h6"></path>
            </svg>
            <span>Reservasi</span>
            <span class="nav-count">8</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('dashboard.menu.services'))
        <a class="nav-item" href="#services">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3.5 14.4 9l6.1.6-4.6 4 1.4 6-5.3-3.2-5.3 3.2 1.4-6-4.6-4L9.6 9 12 3.5Z"></path>
            </svg>
            <span>Layanan</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('dashboard.menu.team'))
        <a class="nav-item" href="#team">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="9" cy="8" r="3.5"></circle>
                <path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 5a3.5 3.5 0 0 1 0 6.8M17.2 14a5.7 5.7 0 0 1 4 5.5"></path>
            </svg>
            <span>Tim &amp; Staf</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('dashboard.menu.customers'))
        <a class="nav-item" href="#customers">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="9" cy="8" r="3.5"></circle>
                <path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 11a3 3 0 1 0 0-6"></path>
            </svg>
            <span>Pelanggan</span>
        </a>
        @endif
        @if (auth()->user()->hasPermission('dashboard.menu.reports'))
        <a class="nav-item" href="#reports">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 20V10m5 10V4m5 16v-7m5 7V7"></path>
            </svg>
            <span>Laporan</span>
        </a>
        @endif
        @if (collect(['guru', 'admin', 'murid'])->contains(fn ($roleKey) => auth()->user()->hasPermission("master_data.{$roleKey}.view")))
        <div class="nav-group {{ request()->routeIs('master-data.*') ? 'nav-group-active' : '' }}">
            <button
                class="nav-item nav-group-heading {{ request()->routeIs('master-data.*') ? 'active' : '' }}"
                type="button"
                aria-expanded="{{ request()->routeIs('master-data.*') ? 'true' : 'false' }}"
                aria-controls="user-submenu"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="9" cy="8" r="3.5"></circle>
                    <path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 11a3 3 0 1 0 0-6M17.2 14a5.7 5.7 0 0 1 4 5.5"></path>
                </svg>
                <span>User Management</span>
                <span class="nav-group-chevron" aria-hidden="true">⌄</span>
            </button>
            <div class="nav-submenu" id="user-submenu" @if (!request()->routeIs('master-data.*')) hidden @endif>
                @foreach (['guru' => 'Guru', 'admin' => 'Admin', 'murid' => 'Murid'] as $roleKey => $label)
                    @if (auth()->user()->hasPermission("master_data.{$roleKey}.view"))
                    <a
                        class="nav-subitem {{ request()->route('role') === $roleKey ? 'active' : '' }}"
                        href="{{ route('master-data.index', ['role' => $roleKey]) }}"
                        @if (request()->route('role') === $roleKey) aria-current="page" @endif
                    >{{ $label }}</a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
        @if (auth()->user()->hasPermission('role_access.view'))
        <a class="nav-item {{ request()->routeIs('role-access.*') ? 'active' : '' }}" href="{{ route('role-access.index') }}" @if (request()->routeIs('role-access.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="8" cy="8" r="3.5"></circle>
                <path d="M2.5 20a5.5 5.5 0 0 1 11 0M15 5h6M18 2v6M16 14h5M16 18h5"></path>
            </svg>
            <span>Role Akses</span>
        </a>
        @endif
    </nav>

    <div class="sidebar-bottom">
        @if (auth()->user()->hasPermission('settings.view'))
        <a class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}" @if (request()->routeIs('settings.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="m19.4 15 .1.1a1.8 1.8 0 0 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 0 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 0 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3h-.2a1.8 1.8 0 0 1 0-3.6h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 0 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 0 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 0 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 0 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3Z"></path>
            </svg>
            <span>Pengaturan</span>
        </a>
        @endif
        <div class="sidebar-help">
            <span class="help-icon" aria-hidden="true">?</span>
            <span><strong>Butuh bantuan?</strong><small>Hubungi tim kami</small></span>
            <span class="help-arrow" aria-hidden="true">→</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="nav-item logout-button" type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10M14 16l4-4-4-4m4 4H9"></path>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
