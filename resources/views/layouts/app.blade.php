<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B1220">
    <title>SKBtrans Monitoring</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
  </head>
  <body>
    @php
      $role = session('role') ?? 'admin';
      $currentRoute = Route::currentRouteName();
      $currentUser = auth()->user();
      $driverSessionId = session('driver_id');
      $driver = \App\Models\Driver::find($driverSessionId);
      $appUser = \App\Models\User::find(session('user_id'));

      $backUrl = null;
      if (str_starts_with($currentRoute, 'master.') && (str_ends_with($currentRoute, '.create') || str_ends_with($currentRoute, '.edit'))) {
        $backUrl = route((string) preg_replace('/\.(create|edit)$/', '.index', $currentRoute));
      } elseif (in_array($currentRoute, ['jadwal.create', 'jadwal.edit'], true)) {
        $backUrl = route('jadwal.index');
      } elseif (in_array($currentRoute, ['akun_kelola.createAdmin', 'akun_kelola.editAdmin'], true)) {
        $backUrl = route('akun_kelola.index', ['tab' => 'admin']);
      } elseif ($currentRoute === 'akun_kelola.history') {
        $backUrl = route('akun_kelola.index');
      } elseif (in_array($currentRoute, ['akun_kelola.createDriver', 'akun_kelola.editDriver'], true)) {
        $backUrl = route('akun_kelola.index', ['tab' => 'driver']);
      } elseif ($currentRoute === 'status_log.show') {
        $backUrl = route('status_log.index');
      }

      $loggedName = $currentUser?->name ?? $driver?->nama_driver ?? $appUser?->name ?? $currentUser?->username ?? $driver?->username ?? $appUser?->username ?? 'Admin';
      $loggedRole = $role === 'driver' ? 'Driver' : 'Admin';
      $loggedUser = $loggedName;
      $loggedInitial = strtoupper(substr(trim($loggedName), 0, 1));
      $pageTitle = match($currentRoute) {
        'dashboard' => 'Dashboard',
        'jadwal.index', 'jadwal.create', 'jadwal.edit' => 'Jadwal',
        'jadwal.store', 'jadwal.update' => 'Jadwal',
        'operasional.index' => 'Operasional',
        'master.transporter.index' => 'Transporter',
        'master.transporter.create', 'master.transporter.edit' => 'Transporter',
        'master.driver.index' => 'Driver',
        'master.driver.create', 'master.driver.edit' => 'Driver',
        'master.kendaraan.index' => 'Kendaraan',
        'master.kendaraan.create', 'master.kendaraan.edit' => 'Kendaraan',
        'master.kategori_material.index' => 'Kategori Material',
        'master.kategori_material.create', 'master.kategori_material.edit' => 'Kategori Material',
        'master.material.index' => 'Material',
        'master.material.create', 'master.material.edit' => 'Material',
        'master.asal.index' => 'Asal',
        'master.asal.create', 'master.asal.edit' => 'Asal',
        'master.tujuan.index' => 'Tujuan',
        'master.tujuan.create', 'master.tujuan.edit' => 'Tujuan',
        'akun_kelola.index' => request()->query('tab', 'admin') === 'driver' ? 'Kelola Driver' : 'Kelola Admin',
        'akun_kelola.createAdmin', 'akun_kelola.editAdmin' => 'Kelola Admin',
        'akun_kelola.createDriver', 'akun_kelola.editDriver' => 'Kelola Driver',
        'data_volume.index' => 'Data Volume',
        'data_timbangan.index' => 'Data Timbangan',
        'status_log.index' => 'Status Log',
        'rekap.index' => 'Rekap',
        'driver.jadwal', 'driver.status' => 'Jadwal Pengiriman Anda',
        default => 'Operational Dashboard'
      };
    @endphp

    @php
      $flashMessages = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'success', 'message' => session('success_message')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
      ])->filter(fn($item) => is_string($item['message'] ?? null) && trim((string) $item['message']) !== '')
        ->map(fn($item) => ['type' => $item['type'] ?? 'success', 'message' => trim((string) $item['message'])])
        ->values()->all();
    @endphp

    <script>
      window.__flashMessages = @json($flashMessages);
      window.__flashMessagesShown = false;
    </script>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    <div class="app-shell {{ $role === 'driver' ? 'app-shell--driver' : '' }}" id="appShell">
      @if($role !== 'driver')
        <aside class="app-sidebar">
          <div class="brand">
            <div class="brand-logo-row">
              <div class="skb-logo skb-logo--compact" aria-label="SKB Trans logo">
                <img src="{{ asset('images/logo.png') }}" alt="SKB Trans" />
              </div>
            </div>

            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Buka atau tutup sidebar" aria-expanded="true">
              <span></span><span></span><span></span>
            </button>
          </div>

          <nav class="sidebar-nav" aria-label="Sidebar navigation">
            @if($role === 'admin')
              <div class="nav-section nav-collapsible is-open">
                <button type="button" class="nav-section-toggle" aria-expanded="true">
                  <span class="nav-group-label">Overview</span>
                  <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="nav-section-body">
                  <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="12" width="7" height="9" rx="1.5"/></svg></span>
                    <span class="nav-text">Dashboard</span>
                  </a>
                  <a href="{{ url('/jadwal') }}" class="nav-item {{ request()->routeIs('jadwal.*') || request()->is('jadwal*') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
                    <span class="nav-text">Jadwal</span>
                  </a>
                  <a href="{{ route('operasional.index') }}" class="nav-item {{ request()->routeIs('operasional.index') ? 'active' : '' }}">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 15h2l1.5-5h9L17 15h4"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/><path d="M7 10V8l3-2h5l2 2v2"/></svg></span>
                    <span class="nav-text">Operasional</span>
                  </a>
                </div>
              </div>

              <div class="nav-section nav-collapsible is-open">
                <button type="button" class="nav-section-toggle" aria-expanded="true">
                  <span class="nav-group-label">Master Data</span>
                  <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="nav-section-body">
                  <a href="{{ route('master.transporter.index') }}" class="nav-item {{ request()->routeIs('master.transporter.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 19a4 4 0 1 1-8 0"/><path d="M12 15V8"/><path d="M9 10h6"/><path d="M5 18a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2"/><path d="M7 7V6a5 5 0 0 1 10 0v1"/></svg></span><span class="nav-text">Transporter</span></a>
                  <a href="{{ route('master.driver.index') }}" class="nav-item {{ request()->routeIs('master.driver.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M4 19c1.5-3 4.2-4.5 8-4.5s6.5 1.5 8 4.5"/></svg></span><span class="nav-text">Driver</span></a>
                  <a href="{{ route('master.kendaraan.index') }}" class="nav-item {{ request()->routeIs('master.kendaraan.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 15h2l1.5-5h9L17 15h4"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/><path d="M7 10V8l3-2h5l2 2v2"/></svg></span><span class="nav-text">Kendaraan</span></a>
                  <a href="{{ route('master.kategori_material.index') }}" class="nav-item {{ request()->routeIs('master.kategori_material.*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-9Z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg></span><span class="nav-text">Kategori Material</span></a>
                  <a href="{{ route('master.material.index') }}" class="nav-item {{ request()->routeIs('master.material.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7l8 4 8-4-8-4Z"/><path d="M4 7v10l8 4 8-4V7"/><path d="M12 11v10"/></svg></span><span class="nav-text">Material</span></a>
                  <a href="{{ route('master.asal.index') }}" class="nav-item {{ request()->routeIs('master.asal.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s6-5.4 6-11a6 6 0 1 0-12 0c0 5.6 6 11 6 11Z"/><circle cx="12" cy="10" r="2.2"/></svg></span><span class="nav-text">Asal</span></a>
                  <a href="{{ route('master.tujuan.index') }}" class="nav-item {{ request()->routeIs('master.tujuan.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s6-5.4 6-11a6 6 0 1 0-12 0c0 5.6 6 11 6 11Z"/><circle cx="12" cy="10" r="2.2"/></svg></span><span class="nav-text">Tujuan</span></a>
                  <a href="{{ route('akun_kelola.index') }}" class="nav-item {{ request()->routeIs('akun_kelola.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/><path d="M4 19c1.4-3 4-4.5 8-4.5s6.6 1.5 8 4.5"/></svg></span><span class="nav-text">Kelola Akun</span></a>
                </div>
              </div>

              <div class="nav-section nav-collapsible is-open">
                <button type="button" class="nav-section-toggle" aria-expanded="true">
                  <span class="nav-group-label">Reporting</span>
                  <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="nav-section-body">
                  <a href="{{ route('data_volume.index') }}" class="nav-item {{ request()->routeIs('data_volume.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5"/><path d="M10 19V9"/><path d="M16 19V7"/><path d="M22 19V3"/></svg></span><span class="nav-text">Data Volume</span></a>
                  <a href="{{ route('data_timbangan.index') }}" class="nav-item {{ request()->routeIs('data_timbangan.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14"/><path d="M7 7V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v2"/><path d="M8 7v8.5A2.5 2.5 0 0 0 10.5 18h3A2.5 2.5 0 0 0 16 15.5V7"/><path d="M9 12h6"/></svg></span><span class="nav-text">Data Timbangan</span></a>
                  <a href="{{ route('status_log.index') }}" class="nav-item {{ request()->routeIs('status_log.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h12"/><path d="M8 12h12"/><path d="M8 18h12"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg></span><span class="nav-text">Status Log</span></a>
                  <a href="{{ route('rekap.index') }}" class="nav-item {{ request()->routeIs('rekap.index') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3.5h7l5 5V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z"/><path d="M14 3.5V9h5"/><path d="M8 13h8M8 17h8"/></svg></span><span class="nav-text">Rekap</span></a>
                </div>
              </div>
            @elseif($role === 'driver')
              <div class="nav-section nav-collapsible is-open">
                <button type="button" class="nav-section-toggle" aria-expanded="true">
                  <span class="nav-group-label">Driver</span>
                  <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="nav-section-body">
                  <a href="{{ route('driver.status') }}" class="nav-item {{ request()->routeIs('driver.status') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg></span><span class="nav-text">Update Status</span></a>
                </div>
              </div>
            @else
              <div class="nav-section nav-collapsible is-open">
                <button type="button" class="nav-section-toggle" aria-expanded="true">
                  <span class="nav-group-label">General</span>
                  <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="nav-section-body">
                  <a href="{{ url('/jadwal') }}" class="nav-item {{ request()->routeIs('jadwal.*') || request()->is('jadwal*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span><span class="nav-text">Jadwal</span></a>
                </div>
              </div>
            @endif
          </nav>

          <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn btn-outline-secondary w-100 sidebar-logout-button" title="Logout" aria-label="Logout">
                <svg class="sidebar-logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/></svg>
                    <span class="sidebar-logout-label">Logout</span>
              </button>
            </form>
          </div>
        </aside>
      @endif

      <div class="app-main">
        <header class="topbar">
          <div class="topbar-inner">
            <div class="page-title">
              <div class="page-heading">
                @if($backUrl)
                  <a href="{{ $backUrl }}" class="page-back-button" title="Kembali" aria-label="Kembali">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M9 12h10"/></svg>
                  </a>
                @endif
                <h1>{{ $pageTitle }}</h1>
              </div>
            </div>

            <div class="topbar-actions">
              <div class="realtime-chip" aria-live="polite">
                <span id="realtimeClock">--</span>
              </div>
              @if($role === 'driver')
                <div class="driver-user-stack">
                  <div class="user-chip driver-user-card" title="{{ $loggedUser }} • {{ $loggedRole }}">
                    <span class="user-avatar driver-user-avatar">{{ $loggedInitial }}</span>
                    <div class="user-meta driver-user-meta">
                      <span class="user-name driver-user-name">{{ $loggedUser }}</span>
                      <span class="user-role driver-user-role">{{ $loggedRole }}</span>
                    </div>
                  </div>
                </div>
              @else
                <div class="user-controls">
                  <div class="user-chip" title="{{ $loggedUser }} • {{ $loggedRole }}">
                    <span class="user-avatar">{{ $loggedInitial }}</span>
                    <div class="user-meta">
                      <span class="user-name">{{ $loggedUser }}</span>
                      <span class="user-role">{{ $loggedRole }}</span>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </header>

        <main class="content-area">
          @yield('content')
        </main>
      </div>
    </div>

    @if($role === 'driver')
      <div class="driver-fab" id="driverFab">
        <div class="driver-fab-menu" id="driverFabMenu" hidden>
          <form method="POST" action="{{ route('logout') }}" class="driver-logout-fab-form">
            @csrf
            <button type="submit" class="btn driver-logout-fab" title="Logout" aria-label="Logout">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/></svg>
            </button>
          </form>
        </div>
        <button type="button" class="btn driver-fab-toggle" id="driverFabToggle" aria-controls="driverFabMenu" aria-expanded="false" title="Buka menu aksi" aria-label="Buka menu aksi">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        </button>
      </div>
    @endif

    <nav class="mobile-nav" aria-label="Mobile navigation">
      @if($role === 'driver')
        <a href="{{ route('driver.status') }}" class="active">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
          Status
        </a>
      @else
        <a href="{{ route('dashboard') }}" class="active">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18h18"/><path d="M7 14V8"/><path d="M12 14V5"/><path d="M17 14v-9"/></svg>
          Home
        </a>
        <a href="{{ url('/jadwal') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 10h18"/></svg>
          Jadwal
        </a>
      @endif
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const clock = document.getElementById('realtimeClock');
        if (clock) {
          const updateClock = () => {
            const now = new Date();
            const value = new Intl.DateTimeFormat('id-ID', {
              weekday: 'long',
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: false
            }).format(now);
            clock.textContent = value;
          };

          updateClock();
          setInterval(updateClock, 1000);
        }

        document.querySelectorAll('.nav-section-toggle').forEach(function (button) {
          const section = button.closest('.nav-collapsible');
          if (!section) return;

          button.addEventListener('click', function () {
            const collapsed = section.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', String(!collapsed));
          });
        });

        const appShell = document.getElementById('appShell');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (appShell && sidebarToggle) {
          const savedState = localStorage.getItem('skbSidebarCollapsed');
          if (savedState === '1') {
            appShell.classList.add('sidebar-collapsed');
            sidebarToggle.setAttribute('aria-expanded', 'false');
          }

          sidebarToggle.addEventListener('click', function () {
            const collapsed = appShell.classList.toggle('sidebar-collapsed');
            sidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            localStorage.setItem('skbSidebarCollapsed', collapsed ? '1' : '0');
          });
        }
      });
    </script>
    @stack('scripts')
  </body>
</html>
