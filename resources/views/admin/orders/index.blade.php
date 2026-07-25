@extends('admin.layouts.admin')

@section('title', 'Orders — Admin')
@section('page-title', 'Orders')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') === 'confirmed' ? 'btn-info' : 'btn-outline-info' }}">Confirmed</a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="btn btn-sm {{ request('status') === 'shipped' ? 'btn-primary' : 'btn-outline-primary' }}">Shipped</a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="btn btn-sm {{ request('status') === 'delivered' ? 'btn-success' : 'btn-outline-success' }}">Delivered</a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">Cancelled</a>
    </div>
    <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by order #, customer name or email..." value="{{ request('search') }}" style="max-width: 320px;">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button>
        @if (request('search'))
            <a href="{{ route('admin.orders.index', ['status' => request('status')]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        @endif
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="fw-bold">#{{ $order->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-dark text-white fw-bold flex-shrink-0"
                                         style="width: 36px; height: 36px; font-size: .8rem;">
                                        {{ substr($order->user?->name ?? 'G', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium small">{{ $order->user?->name ?? 'Guest' }}</div>
                                        <small class="text-muted">{{ $order->user?->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $order->items->count() }}</td>
                            <td class="fw-bold">UGX {{ number_format($order->total_amount) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} me-1">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                                <small class="d-block text-muted">{{ $order->payment_method }}</small>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                    $color = $statusColors[$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td><small class="text-muted">{{ $order->created_at->format('M d, Y') }}<br>{{ $order->created_at->format('h:i A') }}</small></td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-dark">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">No orders yet.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">
    {{ $orders->links() }}
</div>
@endsection