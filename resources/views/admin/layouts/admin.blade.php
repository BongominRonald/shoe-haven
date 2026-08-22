<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Shoe Haven') }}</title>
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @stack('styles')
</head>
<body>
<div class="admin-shell">
    <x-admin.sidebar />

    <div class="sh-sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">
        <header class="admin-topbar d-flex align-items-center justify-content-between px-3 px-lg-4">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button class="sh-collapse-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar" aria-expanded="false" aria-controls="adminSidebar">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
                <div class="admin-page-heading min-w-0">
                    <div class="text-muted small d-none d-md-block">Admin Panel / @yield('page-title', 'Dashboard')</div>
                    <h1 class="text-truncate">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @php($unreadMessages = \App\Models\ContactMessage::where('is_read', false)->count())
                <a href="{{ route('admin.messages.index') }}" class="sh-collapse-btn position-relative text-decoration-none" aria-label="Messages" data-bs-toggle="tooltip" data-bs-title="Messages">
                    <i class="bi bi-bell"></i>
                    @if ($unreadMessages > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ min($unreadMessages, 99) }}</span>
                    @endif
                </a>
                <div class="dropdown">
                    <button class="admin-user border-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" type="button">
                        <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                        <span class="d-none d-md-block text-start">
                            <strong class="d-block small">{{ auth()->user()->name }}</strong>
                            <small class="text-muted">Administrator</small>
                        </span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-shop me-2"></i>View store</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sidebarPrefsModal"><i class="bi bi-sliders me-2"></i>Sidebar preferences</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="admin-content">
            @if (session('status'))
                <x-admin.alert type="success" :message="session('status')" class="mb-3" />
            @endif
            @if ($errors->any())
                <x-admin.alert type="danger" class="mb-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </x-admin.alert>
            @endif
            @yield('content')
        </div>
    </main>
</div>

@include('admin.partials.sidebar-preferences')

<script>
(function () {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');
    const key = 'shoeHavenAdminSidebar';
    const tooltips = [];

    const isMobile = () => window.innerWidth < 992;

    if (!isMobile() && localStorage.getItem(key) === 'collapsed') body.classList.add('sidebar-collapsed');

    if (typeof bootstrap !== 'undefined' && sidebar) {
        sidebar.querySelectorAll('.nav-link[data-bs-title]').forEach(el => {
            const tip = new bootstrap.Tooltip(el, {
                placement: 'right',
                trigger: 'hover focus',
                title: el.getAttribute('data-bs-title'),
                customClass: 'sh-tooltip'
            });
            tip.disable();
            tooltips.push(tip);
        });
    }

    function syncTooltips() {
        const collapsed = body.classList.contains('sidebar-collapsed');
        tooltips.forEach(t => collapsed ? t.enable() : t.disable());
    }
    syncTooltips();

    function closeDrawer() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
        toggle?.setAttribute('aria-expanded', 'false');
    }

    toggle?.addEventListener('click', function () {
        if (isMobile()) {
            const willOpen = !sidebar.classList.contains('open');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
            toggle.setAttribute('aria-expanded', String(willOpen));
            return;
        }
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem(key, body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        syncTooltips();
    });

    overlay?.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar?.classList.contains('open')) closeDrawer();
    });

    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                const original = btn.innerHTML;
                btn.dataset.original = original;
                btn.innerHTML = '<span class="sh-spinner"></span> Processing...';
            }
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>