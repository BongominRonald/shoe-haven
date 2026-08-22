@props(['prefs' => null])

@php
    $prefs = $prefs ?? auth()->user()?->sidebar_prefs ?? [];
@endphp

<div class="sh-sidebar" id="adminSidebar">
    <div class="sh-sidebar-inner">
        <a href="{{ route('admin.dashboard') }}" class="sh-brand text-decoration-none" aria-label="ShoeHaven Administration dashboard">
            <div class="sh-brand-mark">SH</div>
            <div>
                <div class="sh-brand-title">Shoe<span style="color:var(--sh-orange)">Haven</span></div>
                <div class="sh-brand-sub">Administration</div>
            </div>
        </a>

        <nav class="sh-nav nav flex-column" aria-label="Admin navigation">
            @foreach (config('admin-nav', []) as $group)
                <div class="sh-nav-group">
                    <div class="sh-nav-label">{{ $group['label'] }}</div>
                    @foreach ($group['items'] as $item)
                        @php($isActive = request()->routeIs($item['pattern']))
                        <a class="nav-link {{ $isActive ? 'active' : '' }}"
                           href="{{ route($item['route']) }}"
                           data-bs-title="{{ $item['label'] }}"
                           aria-label="{{ $item['label'] }}"
                           @if ($isActive) aria-current="page" @endif>
                            <i class="bi {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sh-sidebar-footer">
            <a class="nav-link" href="{{ route('home') }}" data-bs-title="View Store" aria-label="View Store"><i class="bi bi-shop"></i><span>View Store</span></a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="nav-link w-100 border-0 bg-transparent text-start" data-bs-title="Sign Out" aria-label="Sign Out"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></button>
            </form>
        </div>
    </div>
</div>
