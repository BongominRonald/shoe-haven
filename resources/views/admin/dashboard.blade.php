@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $money = fn ($v) => number_format((float) $v, 0);
    $moneyK = fn ($v) => $v >= 1000000 ? rtrim(rtrim(number_format($v / 1000000, 1), '0'), '.') . 'M' : ($v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1), '0'), '.') . 'K' : number_format((float) $v, 0));
    $pct = fn ($v, $null = '—') => $v === null ? $null : (($v > 0 ? '+' : '') . number_format($v, 1) . '%');
@endphp

@php
    $recentRows = collect($recent_orders ?? [])->map(fn ($o) => [
        '<a href="' . e($o['url']) . '" class="fw-semibold text-decoration-none">' . e($o['number']) . '</a>',
        '<span class="sh-truncate-name">' . e($o['customer']) . '</span>',
        '<span class="sh-amount">UGX ' . $money($o['total']) . '</span>',
        '<span class="sh-badge sh-badge-' . $o['badge'] . '">' . ucfirst($o['status']) . '</span>',
        '<span class="sh-caption">' . e($o['created_at']) . '</span>',
    ])->all();
@endphp

@section('content')
<x-admin.page-header title="Store Overview">
    <form action="{{ route('admin.dashboard') }}" method="GET" class="sh-range-form" id="dashboardRangeForm" aria-label="Dashboard date range">
        <label for="rangeSelect">Period</label>
        <select name="range" id="rangeSelect" class="sh-range-select">
            @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'this-month' => 'This month', 'last-month' => 'Last month', 'this-year' => 'This year', 'all' => 'All time', 'custom' => 'Custom range'] as $val => $label)
                <option value="{{ $val }}" @selected($range['key'] === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <div id="customRangeFields" @if ($range['key'] !== 'custom') hidden @endif class="d-inline-flex align-items-center gap-2">
            <input type="date" name="from" class="sh-date-input" value="{{ request('from') }}" aria-label="From date">
            <span>to</span>
            <input type="date" name="to" class="sh-date-input" value="{{ request('to') }}" aria-label="To date">
        </div>
        <button type="submit" class="sh-btn sh-btn-primary sh-btn-sm" data-loading>Apply</button>
    </form>
</x-admin.page-header>

<div class="mb-3">
    <span class="sh-caption">Showing data for <strong>{{ $range['spanLabel'] }}</strong>
        ({{ $range['from']->format('M j, Y') }} – {{ $range['to']->copy()->subDay()->format('M j, Y') }})
        @if ($range['key'] !== 'all' && $range['key'] !== 'custom')· compared with the previous {{ $range['spanDays'] }} day(s) @endif
    </span>
</div>

@if (isset($kpis['error']))
    <x-admin.alert type="danger" :message="$kpis['error']" class="mb-3" />
@else
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="Revenue" :value="'UGX ' . $moneyK($kpis['revenue'])" icon="bi-wallet2" color="primary"
                :trend="$pct($sales['revenue_pct'] ?? null)" :trendType="($sales['revenue_pct'] ?? 0) > 0 ? 'up' : (($sales['revenue_pct'] ?? 0) < 0 ? 'down' : 'flat')"
                :sub="'prev: UGX ' . $moneyK($kpis['revenue_prev'])" :href="route('admin.orders.index')" />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="Orders" :value="$money($kpis['orders'])" icon="bi-bag-check" color="success"
                :trend="$pct($sales['orders_pct'] ?? null)" :trendType="($sales['orders_pct'] ?? 0) > 0 ? 'up' : (($sales['orders_pct'] ?? 0) < 0 ? 'down' : 'flat')"
                :sub="'prev: ' . $money($kpis['orders_prev'])" :href="route('admin.orders.index')" />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="New customers" :value="$money($kpis['customers'])" icon="bi-person-plus" color="info"
                :trend="$pct($kpis['customers_prev'] ? round((($kpis['customers'] - $kpis['customers_prev']) / $kpis['customers_prev']) * 100, 1) : ($kpis['customers'] > 0 ? null : 0))"
                :trendType="$kpis['customers'] > $kpis['customers_prev'] ? 'up' : ($kpis['customers'] < $kpis['customers_prev'] ? 'down' : 'flat')"
                :sub="'prev: ' . $money($kpis['customers_prev'])" :href="route('admin.users.index')" />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="Products" :value="$money($kpis['products'])" icon="bi-box-seam" color="secondary"
                sub="In catalog" :href="route('admin.products.index')" />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="Pending orders" :value="$money($kpis['pending_orders'])" icon="bi-hourglass-split" color="warning"
                :sub="'Out of stock: ' . $money($kpis['out_of_stock'])" :href="route('admin.orders.index', ['status' => 'pending'])" />
        </div>
        <div class="col-sm-6 col-xl-2">
            <x-admin.stat-card title="Low stock" :value="$money($kpis['low_stock'])" icon="bi-exclamation-diamond" color="danger"
                :sub="'Below 5 units'" :href="route('admin.inventory.index', ['filter' => 'low-stock'])" />
        </div>
    </div>
@endif

@if (isset($chart['error']))
    <x-admin.alert type="danger" :message="$chart['error']" class="mb-3" />
@else
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <x-admin.card title="Sales overview" :subtitle="$range['spanLabel'] . ' — revenue and order volume'">
                <canvas id="salesChart" height="280" aria-label="Sales chart" role="img"
                        data-labels='@json($chart['labels'])'
                        data-revenue='@json($chart['revenue'])'
                        data-orders='@json($chart['orders'])'></canvas>
                <div id="salesChartEmpty" class="d-none text-center py-4">
                    <p class="mb-0 text-muted small">No sales recorded in this period yet.</p>
                </div>
            </x-admin.card>
        </div>
        <div class="col-lg-4">
            <x-admin.card title="Order overview" :subtitle="'Orders by status (' . $money($orders['total']) . ')'">
                <ul class="list-unstyled mb-0">
                    @foreach ($orders['items'] as $status)
                        <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <a href="{{ $status['url'] }}" class="text-decoration-none text-body d-flex align-items-center gap-2">
                                <x-admin.badge :type="$status['badge']">{{ $status['label'] }}</x-admin.badge>
                            </a>
                            <a href="{{ $status['url'] }}" class="text-decoration-none fw-semibold">{{ $money($status['count']) }}</a>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.orders.index') }}" class="sh-btn sh-btn-light w-100 mt-3">View all orders</a>
            </x-admin.card>
        </div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        @if (isset($recent_orders['error']))
            <x-admin.alert type="danger" :message="$recent_orders['error']" class="mb-3" />
        @else
            <x-admin.card title="Recent orders" subtitle="Latest 6 orders">
                <x-admin.table
                    :headers="['Order', 'Customer', 'Total', 'Status', 'Placed']"
                    :rows="$recentRows"
                    empty-message="No orders placed yet."
                    empty-icon="bi-bag"
                    empty-title="No orders yet" />
            </x-admin.card>
        @endif

        @if (isset($inventory['error']))
            <x-admin.alert type="danger" :message="$inventory['error']" class="mb-3" />
        @else
            <x-admin.card title="Inventory overview" subtitle="Current stock levels" class="mt-3">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Products</div>
                            <div class="sh-metric-value">{{ $money($inventory['product_count']) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Total units</div>
                            <div class="sh-metric-value">{{ $money($inventory['total_units']) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Low stock</div>
                            <div class="sh-metric-value text-warning">{{ $money($inventory['low_stock']) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Out of stock</div>
                            <div class="sh-metric-value text-danger">{{ $money($inventory['out_of_stock']) }}</div>
                        </div>
                    </div>
                </div>
                @if (count($inventory['size_stock']) > 0)
                    <div class="mt-3">
                        <div class="sh-section-title"><h3 class="fs-6 mb-2">Stock by size</h3></div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($inventory['size_stock'] as $size)
                                <span class="sh-badge sh-badge-secondary">{{ $size['size'] }} · {{ $money($size['qty']) }} units</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('admin.inventory.index') }}" class="sh-btn sh-btn-light mt-3">Manage inventory</a>
            </x-admin.card>
        @endif
    </div>

    <div class="col-lg-5">
        <x-admin.card title="Attention required" subtitle="Items that need your review">
            @if (count($attention_items) === 0)
                <x-admin.empty-state icon="bi-check2-circle" title="All clear" message="Nothing needs attention right now." />
            @else
                @foreach ($attention_items as $item)
                    <a href="{{ $item['url'] }}" class="sh-attention-item {{ $item['url'] ? '' : 'pe-none' }}">
                        <span class="sh-attention-icon sh-stat-icon-{{ $item['color'] }}"><i class="bi {{ $item['icon'] }}"></i></span>
                        <span class="sh-attention-content">
                            <span class="sh-attention-title">{{ $item['title'] }}</span>
                            <span class="sh-attention-desc d-block">{{ $item['desc'] }}</span>
                        </span>
                        <x-admin.badge :type="$item['priority'] === 'high' ? 'danger' : ($item['priority'] === 'medium' ? 'warning' : 'secondary')">{{ strtoupper($item['priority']) }}</x-admin.badge>
                    </a>
                @endforeach
            @endif
        </x-admin.card>

        @if (isset($best_sellers['error']))
            <x-admin.alert type="danger" :message="$best_sellers['error']" class="mt-3" />
        @else
            <x-admin.card title="Best sellers" :subtitle="$range['spanLabel'] . ' — by units sold'" class="mt-3">
                @if (count($best_sellers) === 0)
                    <x-admin.empty-state icon="bi-trophy" title="No sales yet" message="Best sellers will appear once orders are placed." />
                @else
                    <ol class="list-unstyled mb-0">
                        @foreach ($best_sellers as $i => $bs)
                            <li class="d-flex align-items-center gap-3 py-2 border-bottom">
                                <span class="fw-bold text-muted" style="width:20px;">{{ $i + 1 }}</span>
                                @if ($bs['image'])
                                    <img src="{{ asset('storage/' . $bs['image']) }}" alt="" class="sh-thumb">
                                @else
                                    <span class="sh-thumb d-grid place-items-center text-muted"><i class="bi bi-box-seam"></i></span>
                                @endif
                                <span class="flex-grow-1 min-w-0">
                                    <a href="{{ $bs['url'] }}" class="text-decoration-none text-body fw-semibold sh-truncate-name">{{ $bs['product'] }}</a>
                                    <span class="sh-caption d-block">{{ $bs['sold'] }} sold · UGX {{ $money($bs['revenue']) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-admin.card>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        @if (isset($customers['error']))
            <x-admin.alert type="danger" :message="$customers['error']" />
        @else
            <x-admin.card title="Customer overview" :subtitle="$range['spanLabel']">
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Total</div>
                            <div class="sh-metric-value">{{ $money($customers['total']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">New</div>
                            <div class="sh-metric-value">{{ $money($customers['new']) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sh-metric-tile">
                            <div class="sh-metric-label">Returning</div>
                            <div class="sh-metric-value">{{ $money($customers['returning']) }}</div>
                        </div>
                    </div>
                </div>
                @if (count($customers['recent']) > 0)
                    <div class="sh-section-title"><h3 class="fs-6 mb-2">Recently joined</h3></div>
                    <ul class="list-unstyled mb-0">
                        @foreach ($customers['recent'] as $c)
                            <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                                <x-admin.avatar :name="$c['name']" :size="32" />
                                <span class="flex-grow-1 min-w-0">
                                    <a href="{{ $c['url'] }}" class="text-decoration-none text-body fw-semibold sh-truncate-name">{{ $c['name'] }}</a>
                                    <span class="sh-caption d-block">{{ $c['email'] }}</span>
                                </span>
                                <span class="sh-caption">{{ $c['joined'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('admin.users.index') }}" class="sh-btn sh-btn-light mt-3">View all customers</a>
            </x-admin.card>
        @endif
    </div>

    <div class="col-lg-7">
        @if (isset($activity['error']))
            <x-admin.alert type="danger" :message="$activity['error']" />
        @else
            <x-admin.card title="Recent activity" subtitle="Latest inventory changes">
                @if (count($activity) === 0)
                    <x-admin.empty-state icon="bi-activity" title="No activity yet" message="Inventory changes will appear here." />
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($activity as $a)
                            <li class="d-flex align-items-start gap-3 py-2 border-bottom">
                                <span class="sh-stat-icon sh-stat-icon-{{ ($a['qty'] ?? 0) > 0 ? 'success' : (($a['qty'] ?? 0) < 0 ? 'danger' : 'secondary') }}" style="width:30px;height:30px;font-size:.8rem;">
                                    <i class="bi {{ ($a['qty'] ?? 0) > 0 ? 'bi-arrow-up' : (($a['qty'] ?? 0) < 0 ? 'bi-arrow-down' : 'bi-dash') }}"></i>
                                </span>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="fw-semibold small">{{ $a['user'] }}</span>
                                    <span class="sh-caption d-block">
                                        {{ $a['action'] }} {{ $a['qty'] }} unit(s)
                                        @if ($a['note'])<span class="text-muted">— {{ $a['note'] }}</span>@endif
                                    </span>
                                </span>
                                <span class="sh-caption">{{ $a['created_at'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        @endif
    </div>
</div>

<x-admin.loading label="Loading dashboard…" class="d-none" id="dashboardLoading" />
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const canvas = document.getElementById('salesChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const revenue = JSON.parse(canvas.dataset.revenue || '[]');
    const orders = JSON.parse(canvas.dataset.orders || '[]');
    const empty = document.getElementById('salesChartEmpty');
    const hasData = revenue.some(v => v > 0) || orders.some(v => v > 0);

    if (!hasData) {
        canvas.classList.add('d-none');
        empty.classList.remove('d-none');
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue (UGX)',
                    data: revenue,
                    borderColor: '#e6532a',
                    backgroundColor: 'rgba(230, 83, 42, .10)',
                    fill: true,
                    tension: .35,
                    yAxisID: 'y',
                    pointRadius: 2,
                },
                {
                    label: 'Orders',
                    data: orders,
                    borderColor: '#2a7de6',
                    backgroundColor: 'rgba(42, 125, 230, .08)',
                    fill: true,
                    tension: .35,
                    yAxisID: 'y1',
                    pointRadius: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.dataset.label === 'Revenue (UGX)'
                            ? 'Revenue: UGX ' + Number(ctx.parsed.y).toLocaleString()
                            : 'Orders: ' + ctx.parsed.y,
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v >= 1000000 ? (v/1000000) + 'M' : v >= 1000 ? (v/1000) + 'K' : v } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                x: { ticks: { maxRotation: 45, font: { size: 10 } } }
            }
        }
    });
})();
</script>
@endpush