@extends('admin.layouts.admin')

@section('title', 'Dashboard — ' . config('app.name', 'Shoe Haven'))
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #e8f5e9; color: #2e7d32;"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $total_products }}</div>
                        <small class="text-muted">Total Products</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #e3f2fd; color: #1565c0;"><i class="bi bi-tags"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $total_categories }}</div>
                        <small class="text-muted">Categories</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #fce4ec; color: #c62828;"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $total_users }}</div>
                        <small class="text-muted">Users</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.inventory.index') }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #fff3e0; color: #e65100;"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $low_stock }}</div>
                        <small class="text-muted">Low Stock Items</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="sh-stat-card card bg-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon" style="background: #e8f5e9; color: #2e7d32;"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div class="fs-3 fw-bold">UGX {{ number_format($total_revenue) }}</div>
                    <small class="text-muted">Total Revenue (Paid)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #fce4ec; color: #c62828;"><i class="bi bi-cart-check"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $total_orders }}</div>
                        <small class="text-muted">Total Orders</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="sh-stat-card card bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon" style="background: #fff3e0; color: #e65100;"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $pending_orders }}</div>
                        <small class="text-muted">Pending Orders</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.sh-stat-card { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.sh-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important; }
a .sh-stat-card { color: inherit; }
</style>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Orders by Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap mb-4">
                    @foreach ($orders_by_status as $status => $count)
                        <div class="flex-fill text-center p-3 rounded-3" style="background: #f5f6fa;">
                            <div class="fs-4 fw-bold">{{ $count }}</div>
                            <small class="text-muted text-capitalize">{{ $status }}</small>
                        </div>
                    @endforeach
                </div>
                <canvas id="ordersChart" height="200"></canvas>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
                <script>
                    new Chart(document.getElementById('ordersChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($orders_by_status)) !!},
                            datasets: [{
                                label: 'Orders',
                                data: {!! json_encode(array_values($orders_by_status)) !!},
                                backgroundColor: ['#ffc107','#0d6efd','#6f42c1','#198754','#dc3545'],
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true, plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-sh-orange">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse ($recent_orders as $order)
                    <div class="d-flex justify-content-between align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="fw-bold">#{{ $order->id }}</span>
                            <small class="d-block text-muted">{{ $order->user?->name ?? 'Guest' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold">UGX {{ number_format($order->total_amount) }}</span>
                            <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }} d-block mt-1">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">No orders yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Recent Products</h5>
        <a href="{{ route('admin.products.index') }}" class="btn btn-sh-orange btn-sm">Manage Products</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent_products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}" alt="" style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px;">
                                    <span class="fw-medium">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>UGX {{ number_format($product->price) }}</td>
                            <td>{{ $product->stock?->quantity ?? 0 }}</td>
                            <td>
                                @if ($product->is_new)
                                    <span class="badge bg-success">New</span>
                                @endif
                                @if ($product->discount)
                                    <span class="badge bg-danger">-{{ $product->discount }}%</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
