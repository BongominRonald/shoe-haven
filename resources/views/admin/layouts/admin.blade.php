<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Shoe Haven') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .sh-sidebar { background: var(--sh-black); min-height: 100vh; transition: transform .3s ease; }
        .sh-sidebar .nav-link { color: rgba(255,255,255,0.7); padding: .75rem 1.25rem; font-weight: 500; border-radius: 0; }
        .sh-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .sh-sidebar .nav-link.active { color: var(--sh-orange); background: rgba(255,255,255,0.05); border-right: 3px solid var(--sh-orange); }
        .sh-sidebar .nav-link i { width: 22px; }
        .sh-stat-card { border: none; border-radius: 14px; }
        .sh-stat-card .icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .sh-sidebar-overlay { display: none; }
        @media (max-width: 991.98px) {
            .sh-sidebar { position: fixed; top: 0; left: 0; z-index: 1050; width: 260px; transform: translateX(-100%); }
            .sh-sidebar.open { transform: translateX(0); }
            .sh-sidebar-overlay { display: none; position: fixed; inset: 0; z-index: 1049; background: rgba(0,0,0,0.5); }
            .sh-sidebar-overlay.show { display: block; }
            .sh-sidebar-toggle { display: inline-flex !important; }
        }
        @media (min-width: 992px) {
            .sh-sidebar-toggle { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sh-sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
        <div class="sh-sidebar p-3" id="adminSidebar">
            <a href="{{ route('admin.dashboard') }}" class="d-block text-decoration-none fs-5 fw-bold text-white mb-4 px-3 py-2">
                Shoe<span style="color: var(--sh-orange);">Haven</span>
                <small class="d-block text-white-50" style="font-size: .7rem;">Admin Panel</small>
            </a>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                    <i class="bi bi-box-seam"></i> Products
                </a>
                <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}">
                    <i class="bi bi-clipboard-data"></i> Inventory
                </a>
                <a class="nav-link {{ request()->routeIs('admin.hero.*') ? 'active' : '' }}" href="{{ route('admin.hero.edit') }}">
                    <i class="bi bi-image"></i> Hero Section
                </a>
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <i class="bi bi-bag-check"></i> Orders
                </a>
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i> Users
                </a>
                <hr style="border-color: rgba(255,255,255,0.1);">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="bi bi-arrow-left"></i> Back to Site
                </a>
            </nav>
        </div>

        <div class="flex-grow-1" style="min-height: 100vh; background: #f5f6fa;">
            <nav class="navbar navbar-light bg-white shadow-sm px-4">
                <div class="d-flex align-items-center gap-3">
                    <button class="sh-sidebar-toggle btn btn-sm btn-outline-dark" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="fw-bold">@yield('page-title', 'Dashboard')</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-dark btn-sm">Logout</button>
                    </form>
                </div>
            </nav>

            <div class="p-3">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.remove('open');
            this.classList.remove('show');
        });
    </script>
    @stack('scripts')
    @vite(['resources/js/app.js'])
</body>
</html>
