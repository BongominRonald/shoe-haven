@extends('layouts.public')

@section('title', 'Checkout — ' . config('app.name', 'Shoe Haven'))

@section('content')
<div class="container py-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('cart.index') }}" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Checkout</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card bg-dark border-0 p-4">
                <h5 class="fw-bold mb-4">Delivery Details</h5>
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number (MTN / Airtel)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+256 700 000 000" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street, city, landmark" required>{{ old('address') }}</textarea>
                    </div>
                    <hr class="text-secondary">
                    <h6 class="fw-bold mb-3">Payment Method</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="mtn" value="mtn" checked>
                        <label class="form-check-label" for="mtn">
                            <i class="bi bi-phone"></i> MTN Mobile Money
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="airtel" value="airtel">
                        <label class="form-check-label" for="airtel">
                            <i class="bi bi-phone"></i> Airtel Money
                        </label>
                    </div>
                    <button type="submit" class="btn btn-sh-orange w-100 mt-3 btn-lg">Place Order</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sh-summary p-4">
                <h5 class="mb-4">Order Summary</h5>
                @foreach ($products as $item)
                    @php $qty = $cart[$item->id]['quantity']; $lineTotal = $item->price * $qty; @endphp
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ Str::startsWith($item->image, 'http') ? $item->image : asset($item->image) }}"
                                 alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                            <div>
                                <span class="small fw-medium">{{ $item->name }}</span>
                                <small class="d-block text-white-50">Qty: {{ $qty }}</small>
                            </div>
                        </div>
                        <span class="fw-medium">UGX {{ number_format($lineTotal) }}</span>
                    </div>
                @endforeach
                <hr style="border-color: rgba(255,255,255,0.15);">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Subtotal</span>
                    <span>UGX {{ number_format($total) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Delivery</span>
                    <span class="text-success">{{ $total >= 200000 ? 'Free' : 'UGX 20,000' }}</span>
                </div>
                <hr style="border-color: rgba(255,255,255,0.15);">
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span style="color: var(--sh-orange);">UGX {{ number_format($total >= 200000 ? $total : $total + 20000) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
