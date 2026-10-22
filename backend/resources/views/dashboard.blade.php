<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta
            name="description"
            content="Dashboard Effata Fun Learning untuk memantau reservasi dan operasional harian."
        />
        <title>Dashboard | Effata Fun Learning</title>
        @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/dashboard.css', 'resources/js/dashboard.js'])
    </head>
    <body class="dashboard-page">
        <aside class="sidebar" id="sidebar">
            <a class="dashboard-brand" href="{{ route('dashboard') }}" aria-label="Effata Fun Learning, dashboard">
                <span class="brand-mark">E</span>
                <span class="brand-name">Effata <span>Fun Learning</span></span>
            </a>

            <p class="sidebar-label">MENU UTAMA</p>
            <nav class="sidebar-nav" aria-label="Menu utama">
                @if (auth()->user()->hasPermission('dashboard.view'))
                <a class="nav-item active" href="{{ route('dashboard') }}" aria-current="page">
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
                <a class="nav-item" href="{{ route('learning-classes.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <path d="M8 2.5v3M16 2.5v3M3 9h18M7 13h4M7 16h7"></path>
                    </svg>
                    <span>Kelas</span>
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
                <div class="nav-group">
                    <button class="nav-item nav-group-heading" type="button" aria-expanded="false" aria-controls="dashboard-user-submenu">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="8" r="3.5"></circle>
                            <path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 11a3 3 0 1 0 0-6M17.2 14a5.7 5.7 0 0 1 4 5.5"></path>
                        </svg>
                        <span>User Management</span>
                        <span class="nav-group-chevron" aria-hidden="true">⌄</span>
                    </button>
                    <div class="nav-submenu" id="dashboard-user-submenu" hidden>
                        @foreach (['guru' => 'Guru', 'admin' => 'Admin', 'murid' => 'Murid'] as $roleKey => $label)
                            @if (auth()->user()->hasPermission("master_data.{$roleKey}.view"))
                            <a class="nav-subitem {{ request()->route('role') === $roleKey ? 'active' : '' }}" href="{{ route('master-data.index', ['role' => $roleKey]) }}">{{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @if (auth()->user()->hasPermission('role_access.view'))
                <a class="nav-item" href="{{ route('role-access.index') }}">
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
                <a class="nav-item" href="{{ route('settings.index') }}">
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

        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <main class="dashboard-main">
            <header class="topbar">
                <button
                    class="mobile-menu-button"
                    id="mobile-menu-button"
                    type="button"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="sidebar"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"></path>
                    </svg>
                </button>
                <div class="topbar-title">
                    <p>Effata Fun Learning <span>/</span> Dashboard</p>
                </div>
                <div class="topbar-actions">
                    <label class="search-box">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="10.8" cy="10.8" r="6.3"></circle>
                            <path d="m16 16 4.5 4.5"></path>
                        </svg>
                        <input type="search" placeholder="Cari apa saja..." aria-label="Cari" />
                        <span class="search-shortcut">⌘ K</span>
                    </label>
                    @if (auth()->user()->hasPermission('dashboard.actions.notifications'))
                    <button class="icon-button notification-button" type="button" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"></path>
                        </svg>
                        <span class="notification-dot"></span>
                    </button>
                    @endif
                    <div class="topbar-divider"></div>
                    @if (auth()->user()->hasPermission('dashboard.actions.profile'))
                    <button class="profile-button" type="button" aria-label="Profil {{ auth()->user()->name }}">
                        <span class="avatar avatar-admin">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}</small></span>
                        <span class="profile-chevron" aria-hidden="true">⌄</span>
                    </button>
                    @endif
                </div>
            </header>

            <div class="dashboard-content">
                <section class="welcome-row">
                    <div>
                        <p class="dashboard-eyebrow" id="current-date">HARI INI</p>
                        <h1>Selamat pagi, {{ auth()->user()->name }} <span>👋</span></h1>
                        <p>Berikut ringkasan aktivitas salon hari ini.</p>
                    </div>
                    @if (auth()->user()->hasPermission('dashboard.actions.create_appointment'))
                    <a class="primary-action" href="#appointments">
                        <span aria-hidden="true">+</span> Buat reservasi
                    </a>
                    @endif
                </section>

                <section class="stats-grid" aria-label="Ringkasan hari ini">
                    <article class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-icon icon-plum" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3.5" y="5.5" width="17" height="15" rx="2"></rect>
                                    <path d="M7.5 3.5v4M16.5 3.5v4M3.5 10h17"></path>
                                </svg>
                            </span>
                            <span class="trend positive">↗ 12,5%</span>
                        </div>
                        <p>Total reservasi</p>
                        <div class="stat-value">24 <span>reservasi</span></div>
                        <small>Dibanding 21 reservasi kemarin</small>
                    </article>
                    <article class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-icon icon-rose" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3v18m5-14.5c0-1.4-1.8-2.5-4-2.5s-4 1.1-4 2.5 1.8 2.5 4 2.5 4 1.1 4 2.5-1.8 2.5-4 2.5-4-1.1-4-2.5"></path>
                                </svg>
                            </span>
                            <span class="trend positive">↗ 8,2%</span>
                        </div>
                        <p>Pendapatan hari ini</p>
                        <div class="stat-value">Rp 4,8 <span>jt</span></div>
                        <small>Dari 18 transaksi selesai</small>
                    </article>
                    <article class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-icon icon-sage" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="9" cy="8" r="3.5"></circle>
                                    <path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 11a3 3 0 1 0 0-6M17.2 14a5.7 5.7 0 0 1 4 5.5"></path>
                                </svg>
                            </span>
                            <span class="trend positive">↗ 5,1%</span>
                        </div>
                        <p>Pelanggan baru</p>
                        <div class="stat-value">8 <span>orang</span></div>
                        <small>Bulan ini ada 46 pelanggan baru</small>
                    </article>
                    <article class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-icon icon-gold" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3.5 14.4 9l6.1.6-4.6 4 1.4 6-5.3-3.2-5.3 3.2 1.4-6-4.6-4L9.6 9 12 3.5Z"></path>
                                </svg>
                            </span>
                            <span class="rating-label">Bulan ini</span>
                        </div>
                        <p>Rating pelanggan</p>
                        <div class="stat-value">4,9 <span class="rating-stars">★</span></div>
                        <small>Dari 128 ulasan pelanggan</small>
                    </article>
                </section>

                <section class="dashboard-panels">
                    @if (auth()->user()->hasPermission('dashboard.menu.reports'))
                    <article class="panel revenue-panel" id="reports">
                        <div class="panel-heading">
                            <div>
                                <h2>Ringkasan pendapatan</h2>
                                <p>Pantau performa pendapatan salon</p>
                            </div>
                            @if (auth()->user()->hasPermission('dashboard.actions.select_report_period'))
                            <button class="select-button" type="button">
                                7 hari terakhir <span aria-hidden="true">⌄</span>
                            </button>
                            @endif
                        </div>
                        <div class="revenue-total">
                            <strong>Rp 28.450.000</strong>
                            <span class="trend positive">↗ 14,8%</span>
                            <small>vs. periode sebelumnya</small>
                        </div>
                        <div class="chart" role="img" aria-label="Grafik pendapatan selama tujuh hari terakhir">
                            <div class="chart-y-labels"><span>8 jt</span><span>6 jt</span><span>4 jt</span><span>2 jt</span><span>0</span></div>
                            <div class="chart-content">
                                <div class="chart-grid">
                                    <i></i><i></i><i></i><i></i><i></i>
                                </div>
                                <div class="chart-bars">
                                    <div class="chart-column"><span class="bar" style="--bar-height: 42%"></span><small>Sen</small></div>
                                    <div class="chart-column"><span class="bar" style="--bar-height: 65%"></span><small>Sel</small></div>
                                    <div class="chart-column"><span class="bar" style="--bar-height: 52%"></span><small>Rab</small></div>
                                    <div class="chart-column"><span class="bar" style="--bar-height: 78%"></span><small>Kam</small></div>
                                    <div class="chart-column"><span class="bar" style="--bar-height: 59%"></span><small>Jum</small></div>
                                    <div class="chart-column"><span class="bar" style="--bar-height: 91%"></span><small>Sab</small></div>
                                    <div class="chart-column"><span class="bar today" style="--bar-height: 72%"></span><small>Min</small></div>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endif

                    @if (auth()->user()->hasPermission('dashboard.menu.services'))
                    <article class="panel service-panel" id="services">
                        <div class="panel-heading">
                            <div>
                                <h2>Layanan terpopuler</h2>
                                <p>Paling banyak dipesan bulan ini</p>
                            </div>
                            @if (auth()->user()->hasPermission('dashboard.actions.view_services'))
                                <a class="panel-link" href="#services">Lihat semua</a>
                            @endif
                        </div>
                        <div class="service-list">
                            <div class="service-row">
                                <span class="service-number">01</span>
                                <span class="service-color service-color-one"></span>
                                <span class="service-copy"><strong>Hair Coloring</strong><small>64 reservasi</small></span>
                                <strong class="service-share">32%</strong>
                            </div>
                            <div class="service-row">
                                <span class="service-number">02</span>
                                <span class="service-color service-color-two"></span>
                                <span class="service-copy"><strong>Hair Treatment</strong><small>48 reservasi</small></span>
                                <strong class="service-share">24%</strong>
                            </div>
                            <div class="service-row">
                                <span class="service-number">03</span>
                                <span class="service-color service-color-three"></span>
                                <span class="service-copy"><strong>Hair Cut &amp; Styling</strong><small>37 reservasi</small></span>
                                <strong class="service-share">19%</strong>
                            </div>
                            <div class="service-row">
                                <span class="service-number">04</span>
                                <span class="service-color service-color-four"></span>
                                <span class="service-copy"><strong>Manicure &amp; Pedicure</strong><small>29 reservasi</small></span>
                                <strong class="service-share">14%</strong>
                            </div>
                        </div>
                    </article>
                    @endif
                </section>

                @if (auth()->user()->hasPermission('dashboard.menu.appointments'))
                <section class="panel appointments-panel" id="appointments">
                    <div class="panel-heading appointment-heading">
                        <div>
                            <h2>Reservasi hari ini <span class="appointment-count">8</span></h2>
                            <p>Daftar jadwal kunjungan pelanggan hari ini</p>
                        </div>
                        @if (auth()->user()->hasPermission('dashboard.actions.view_appointments'))
                            <a class="panel-link" href="#appointments">Lihat semua reservasi <span aria-hidden="true">→</span></a>
                        @endif
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Layanan</th>
                                    <th>Stylist</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="customer-cell"><span class="avatar avatar-rose">NA</span><span><strong>Nadia Anindya</strong><small>nadia.a@email.com</small></span></span></td>
                                    <td>Hair Coloring</td>
                                    <td><span class="stylist-cell"><span class="avatar avatar-sage">S</span> Siska</span></td>
                                    <td>09.00</td>
                                    <td><span class="status-pill status-progress"><i></i>Berlangsung</span></td>
                                    @if (auth()->user()->hasPermission('dashboard.actions.manage_appointments'))
                                        <td><button class="row-menu" type="button" aria-label="Aksi Nadia Anindya">···</button></td>
                                    @endif
                                </tr>
                                <tr>
                                    <td><span class="customer-cell"><span class="avatar avatar-gold">MP</span><span><strong>Maya Putri</strong><small>maya.p@email.com</small></span></span></td>
                                    <td>Hair Treatment</td>
                                    <td><span class="stylist-cell"><span class="avatar avatar-blue">R</span> Rani</span></td>
                                    <td>10.30</td>
                                    <td><span class="status-pill status-confirmed"><i></i>Dikonfirmasi</span></td>
                                    @if (auth()->user()->hasPermission('dashboard.actions.manage_appointments'))
                                        <td><button class="row-menu" type="button" aria-label="Aksi Maya Putri">···</button></td>
                                    @endif
                                </tr>
                                <tr>
                                    <td><span class="customer-cell"><span class="avatar avatar-blue">CL</span><span><strong>Clara Lestari</strong><small>clara.l@email.com</small></span></span></td>
                                    <td>Hair Cut &amp; Styling</td>
                                    <td><span class="stylist-cell"><span class="avatar avatar-plum">D</span> Dewi</span></td>
                                    <td>11.00</td>
                                    <td><span class="status-pill status-waiting"><i></i>Menunggu</span></td>
                                    @if (auth()->user()->hasPermission('dashboard.actions.manage_appointments'))
                                        <td><button class="row-menu" type="button" aria-label="Aksi Clara Lestari">···</button></td>
                                    @endif
                                </tr>
                                <tr>
                                    <td><span class="customer-cell"><span class="avatar avatar-sage">AS</span><span><strong>Amelia Safitri</strong><small>amelia.s@email.com</small></span></span></td>
                                    <td>Manicure &amp; Pedicure</td>
                                    <td><span class="stylist-cell"><span class="avatar avatar-rose">L</span> Lina</span></td>
                                    <td>13.30</td>
                                    <td><span class="status-pill status-confirmed"><i></i>Dikonfirmasi</span></td>
                                    @if (auth()->user()->hasPermission('dashboard.actions.manage_appointments'))
                                        <td><button class="row-menu" type="button" aria-label="Aksi Amelia Safitri">···</button></td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif

                <footer class="dashboard-footer">© 2026 Effata Fun Learning <span>·</span> Dibuat dengan sepenuh hati</footer>
            </div>
        </main>
    </body>
</html>
