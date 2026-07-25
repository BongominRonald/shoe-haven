@extends('admin.layouts.admin')

@section('title', 'Products — ' . config('app.name', 'Shoe Haven'))
@section('page-title', 'Products')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex gap-2" style="max-width: 340px;">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or brand..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button>
        @if (request('search'))
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        @endif
    </form>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">{{ $products->total() }} products</span>
        <a href="{{ route('admin.products.create') }}" class="btn btn-sh-orange btn-sm">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
    @forelse ($products as $product)
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div style="height: 180px; overflow: hidden; border-radius: 8px 8px 0 0;">
                    <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}"
                         alt="{{ $product->name }}"
                         class="w-100 h-100"
                         style="object-fit: cover;">
                </div>
                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-semibold mb-1 text-truncate">{{ $product->name }}</h6>
                    <small class="text-muted mb-1">{{ $product->brand }} &middot; {{ $product->category->name ?? '-' }}</small>
                    <div class="fw-bold mb-2" style="color: var(--sh-orange);">UGX {{ number_format($product->price) }}</div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $product->stock?->quantity < 10 ? 'bg-danger' : 'bg-secondary' }}">
                            Stock: {{ $product->stock?->quantity ?? 0 }}
                        </span>
                        @if ($product->is_new)
                            <span class="badge bg-success">New</span>
                        @endif
                        @if ($product->discount)
                            <span class="badge bg-danger">-{{ $product->discount }}%</span>
                        @endif
                    </div>
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete {{ $product->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">No products yet.</div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection
