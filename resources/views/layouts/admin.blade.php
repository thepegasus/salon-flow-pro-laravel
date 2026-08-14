<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SalonFlow Pro')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/styles.css') }}">
</head>
<body class="sfp-body">
    <div class="sfp-shell">
        <div class="sfp-frame">

            <div class="sfp-topbar">
                <div class="sfp-brand">
                    <div class="sfp-brand-mark">{{ strtoupper(substr($tenant->name ?? 'S', 0, 1)) }}</div>
                    <div class="sfp-brand-text">
                        <div class="sfp-brand-name">{{ $tenant->name ?? 'SalonFlow Pro' }}</div>
                    </div>
                </div>
                <div class="sfp-topbar-actions">
                    @can('appointments.create')
                        <a href="{{ $tenantUrl->route('appointments.create') }}" class="sfp-btn-dark">+ New booking</a>
                    @endcan
                    <form action="{{ $tenantUrl->route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sfp-avatar-btn" title="Sign out">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</button>
                    </form>
                </div>
            </div>

            <div class="sfp-body-row">
                <nav class="sfp-sidebar">
                    <div class="sfp-sidebar-label">STUDIO</div>

                    <a href="{{ $tenantUrl->route('tenant.dashboard') }}" class="sfp-nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                        <span class="sfp-nav-bar"></span>Dashboard
                    </a>

                    @can('appointments.view')
                        <a href="{{ $tenantUrl->route('appointments.index') }}" class="sfp-nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Calendar
                        </a>
                        <a href="{{ $tenantUrl->route('walkIns.index') }}" class="sfp-nav-item {{ request()->routeIs('walkIns.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Walk-ins
                        </a>
                        <a href="{{ $tenantUrl->route('bridalEngagements.index') }}" class="sfp-nav-item {{ request()->routeIs('bridalEngagements.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Bridal &amp; events
                        </a>
                    @endcan

                    @can('billing.view')
                        <a href="{{ $tenantUrl->route('bills.index') }}" class="sfp-nav-item {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Billing
                        </a>
                    @endcan

                    @can('clients.view')
                        <a href="{{ $tenantUrl->route('clients.index') }}" class="sfp-nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Clients
                        </a>
                    @endcan

                    @can('services.view')
                        <a href="{{ $tenantUrl->route('services.index') }}" class="sfp-nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Services
                        </a>
                    @endcan

                    @can('staff.view')
                        <a href="{{ $tenantUrl->route('staff.index') }}" class="sfp-nav-item {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                            <span class="sfp-nav-bar"></span>Staff &amp; roster
                        </a>
                    @endcan

                    <div class="sfp-sidebar-footer">
                        Role: <strong>{{ auth()->user()->getRoleNames()->first() ?? '—' }}</strong>
                    </div>
                </nav>

                <main class="sfp-content">
                    @if (session('status'))
                        <div class="sfp-alert-success">{{ session('status') }}</div>
                    @endif

                    @yield('content')
                </main>
            </div>

        </div>
    </div>

    <script src="{{ asset('admin/js/scripts.js') }}"></script>
    @yield('scripts')
</body>
</html>
