@extends('admin.layouts.admin')

@section('title', 'Inventory — ' . config('app.name', 'Shoe Haven'))
@section('page-title', 'Inventory Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">{{ $products->count() }} products total</p>
    <a href="{{ route('admin.products.create') }}" class="btn btn-sh-orange">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
</div>

@if ($outOfStock->isNotEmpty())
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong>{{ $outOfStock->count() }} product(s) out of stock.</strong>
            @foreach ($outOfStock as $p)
                <span class="badge bg-danger me-1">{{ $p->name }}</span>
            @endforeach
        </div>
    </div>
@endif

@if ($lowStock->isNotEmpty())
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-bell-fill fs-5"></i>
        <div>
            <strong>{{ $lowStock->count() }} product(s) low on stock ({{ $lowStockThreshold }} or fewer).</strong>
            @foreach ($lowStock as $p)
                <span class="badge bg-warning text-dark me-1">{{ $p->name }} ({{ $p->stock?->quantity ?? 0 }} left)</span>
            @endforeach
        </div>
    </div>
@endif

<ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-medium" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">
            <i class="bi bi-box-seam me-2"></i>Stock Levels
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-medium" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
            <i class="bi bi-clock-history me-2"></i>History
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="stock">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php $qty = $product->stock?->quantity ?? 0; @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}"
                                                 alt="" style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px;">
                                            <span class="fw-medium">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $product->brand }}</td>
                                    <td>{{ $product->category->name ?? '-' }}</td>
                                    <td class="text-end fw-medium">{{ $qty }} units</td>
                                    <td class="text-end">
                                        @if ($qty === 0)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif ($qty <= $lowStockThreshold)
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center gap-2 justify-content-end flex-wrap">
                                            <form action="{{ route('admin.inventory.update-stock', $product) }}" method="POST"
                                                  class="d-inline-flex align-items-center gap-2">
                                                @csrf
                                                <input type="number" name="quantity" value="{{ $qty }}" min="0"
                                                       class="form-control form-control-sm" style="width: 70px;" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Edit product">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $product->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete product"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="history">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($history->isEmpty())
                    <div class="text-center text-muted py-5">No inventory changes recorded yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Change</th>
                                    <th>Previous</th>
                                    <th>New</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history as $entry)
                                    <tr>
                                        <td class="text-muted small">{{ $entry->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="fw-medium">{{ $entry->product->name ?? 'Deleted' }}</td>
                                        <td>
                                            @if ($entry->change_amount > 0)
                                                <span class="text-success"><i class="bi bi-arrow-up me-1"></i>+{{ $entry->change_amount }}</span>
                                            @elseif ($entry->change_amount < 0)
                                                <span class="text-danger"><i class="bi bi-arrow-down me-1"></i>{{ $entry->change_amount }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>{{ $entry->previous_quantity }}</td>
                                        <td>{{ $entry->new_quantity }}</td>
                                        <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $entry->change_type) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
