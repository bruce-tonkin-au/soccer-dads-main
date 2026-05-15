@extends('layouts.app')
@section('title', 'Cart — Soccer Dads Store')

@push('styles')
<style>
    .cart-wrap {
        max-width: 720px;
        margin: 0 auto;
        padding: 3rem 2rem;
    }
    .cart-heading {
        font-size: 26px;
        font-weight: 700;
        color: #262c39;
        margin-bottom: 1.5rem;
    }
    .cart-item {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1rem;
    }
    .cart-item img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        flex-shrink: 0;
    }
    .cart-item-info {
        flex: 1;
        min-width: 0;
    }
    .cart-item-name {
        font-size: 15px;
        font-weight: 600;
        color: #262c39;
        text-decoration: none;
        display: block;
        margin-bottom: 4px;
    }
    .cart-item-name:hover { color: #458bc8; }
    .cart-item-price {
        font-size: 13px;
        color: #888;
    }
    .cart-item-line {
        font-size: 17px;
        font-weight: 700;
        color: #262c39;
        white-space: nowrap;
    }
    .cart-total-bar {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .btn-remove {
        background: none;
        border: none;
        color: #ccc;
        cursor: pointer;
        font-size: 16px;
        padding: 4px 8px;
        border-radius: 6px;
        transition: color 0.15s;
        flex-shrink: 0;
    }
    .btn-remove:hover { color: #e24b4a; }
    .btn-checkout {
        width: 100%;
        padding: 14px 24px;
        background: #262c39;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: background 0.2s;
    }
    .btn-checkout:hover { background: #1a1f2a; }
</style>
@endpush

@section('content')

<div style="background:#f8f8f8; min-height:80vh; padding-bottom:4rem;">
    <div class="cart-wrap">

        <h1 class="cart-heading">
            <i class="fa-solid fa-cart-shopping" style="font-size:22px; margin-right:8px;"></i>
            Your cart
        </h1>

        @if(session('error'))
        <div style="background:#fff3f3; border:1px solid #e24b4a; color:#262c39; padding:12px 16px; border-radius:8px; font-size:14px; margin-bottom:1rem;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
        @endif

        @if($items->isEmpty())
        <div style="text-align:center; padding:4rem 0; color:#aaa;">
            <i class="fa-solid fa-cart-shopping" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
            <p style="font-size:17px; margin-bottom:1.5rem;">Your cart is empty.</p>
            <a href="/store" style="display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:#262c39; color:#fff; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none;">
                <i class="fa-solid fa-bag-shopping"></i> Browse the store
            </a>
        </div>
        @else

        @foreach($items as $item)
        <div class="cart-item">
            @if($item->displayImage)
            <img src="{{ $item->displayImage }}" alt="{{ $item->productName }}">
            @else
            <div style="width:72px; height:72px; background:#f4f4f4; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#ccc; flex-shrink:0;">
                <i class="fa-solid fa-image"></i>
            </div>
            @endif

            <div class="cart-item-info">
                <a href="/store/{{ $item->productSlug }}" class="cart-item-name">{{ $item->productName }}</a>
                <div class="cart-item-price">
                    ${{ number_format($item->productPrice, 2) }} × {{ $item->quantity }}
                </div>
            </div>

            <div class="cart-item-line">${{ number_format($item->lineTotal, 2) }}</div>

            <form method="POST" action="/store/cart/remove">
                @csrf
                <input type="hidden" name="productID" value="{{ $item->productID }}">
                <button type="submit" class="btn-remove" title="Remove">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </form>
        </div>
        @endforeach

        <div class="cart-total-bar">
            <span style="font-size:16px; font-weight:600; color:#555;">Total</span>
            <span style="font-size:22px; font-weight:700; color:#262c39;">${{ number_format($total, 2) }} <span style="font-size:13px; color:#aaa; font-weight:400;">AUD</span></span>
        </div>

        <form method="POST" action="/store/cart/checkout">
            @csrf

            @if(!session('player_id'))
            <div style="background:#f8f8f8; border:1px solid #e8e8e8; border-radius:12px; padding:1.25rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#888; margin-bottom:12px;">Your details</p>

                <div style="margin-bottom:10px;">
                    <input type="text" name="guestName" placeholder="Full name *"
                           value="{{ old('guestName') }}"
                           style="width:100%; border:1px solid {{ $errors->has('guestName') ? '#e24b4a' : '#e8e8e8' }}; border-radius:8px; padding:10px 14px; font-size:15px; color:#262c39; outline:none;">
                    @error('guestName')
                    <p style="color:#e24b4a; font-size:13px; margin-top:4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom:10px;">
                    <input type="email" name="guestEmail" placeholder="Email address *"
                           value="{{ old('guestEmail') }}"
                           style="width:100%; border:1px solid {{ $errors->has('guestEmail') ? '#e24b4a' : '#e8e8e8' }}; border-radius:8px; padding:10px 14px; font-size:15px; color:#262c39; outline:none;">
                    @error('guestEmail')
                    <p style="color:#e24b4a; font-size:13px; margin-top:4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="tel" name="guestPhone" placeholder="Phone (optional)"
                           value="{{ old('guestPhone') }}"
                           style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 14px; font-size:15px; color:#262c39; outline:none;">
                </div>
            </div>
            @endif

            <button type="submit" class="btn-checkout">
                <i class="fa-brands fa-stripe-s"></i> Checkout — ${{ number_format($total, 2) }}
            </button>
        </form>

        <p style="font-size:12px; color:#aaa; text-align:center; margin-top:10px;">
            Secure checkout via Stripe
        </p>

        <a href="/store" style="display:block; text-align:center; margin-top:1rem; color:#888; font-size:14px; text-decoration:none;">
            <i class="fa-solid fa-arrow-left"></i> Continue shopping
        </a>

        @endif
    </div>
</div>

@endsection
