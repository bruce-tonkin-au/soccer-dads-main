@extends('layouts.app')
@section('title', $product->productName . ' — Soccer Dads Store')

@push('styles')
<style>
    .product-detail {
        max-width: 900px;
        margin: 0 auto;
        padding: 3rem 2rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
    }
    .product-image-wrap {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e8e8e8;
    }
    .product-image-wrap img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        display: block;
    }
    .product-info {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .product-breadcrumb {
        font-size: 13px;
        color: #aaa;
    }
    .product-breadcrumb a {
        color: #aaa;
        text-decoration: none;
    }
    .product-breadcrumb a:hover {
        color: #262c39;
    }
    .product-name {
        font-size: 28px;
        font-weight: 700;
        color: #262c39;
        line-height: 1.2;
    }
    .product-price {
        font-size: 32px;
        font-weight: 700;
        color: #888;
    }
    .product-description {
        font-size: 15px;
        color: #555;
        line-height: 1.7;
    }
    .product-stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 20px;
    }
    .badge-instock  { background: #f4f4f4; color: #888; }
    .badge-outofstock { background: #f4f4f4; color: #aaa; }
    .qty-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .qty-input {
        width: 72px;
        padding: 8px 10px;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        font-size: 15px;
        text-align: center;
        color: #262c39;
        outline: none;
    }
    .qty-input:focus { border-color: #458bc8; }
    .btn-buynow {
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
    .btn-buynow:hover { background: #1a1f2a; }
    .btn-addtocart {
        width: 100%;
        padding: 12px 24px;
        background: #fff;
        color: #262c39;
        border: 1.5px solid #262c39;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: background 0.2s;
    }
    .btn-addtocart:hover { background: #f4f4f4; }
    @media (max-width: 700px) {
        .product-detail {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 2rem 1.25rem;
        }
    }
</style>
@endpush

@section('content')

<div style="background:#f8f8f8; min-height:80vh; padding-bottom:4rem;">
    <div class="product-detail">
        <div class="product-image-wrap">
            <img src="{{ $product->productImage }}" alt="{{ $product->productName }}">
        </div>

        <div class="product-info">
            <div class="product-breadcrumb">
                <a href="/store"><i class="fa-solid fa-bag-shopping"></i> Store</a>
                &nbsp;/&nbsp;
                {{ $product->productName }}
            </div>

            <h1 class="product-name">{{ $product->productName }}</h1>

            <div class="product-price">${{ number_format($product->productPrice, 2) }}</div>

            @if($product->productStock > 0)
            <div>
                <span class="product-stock-badge badge-instock">
                    <i class="fa-solid fa-circle-check"></i> In stock ({{ $product->productStock }} available)
                </span>
            </div>
            @else
            <div>
                <span class="product-stock-badge badge-outofstock">
                    <i class="fa-solid fa-circle-xmark"></i> Out of stock
                </span>
            </div>
            @endif

            @if($product->productDescription)
            <p class="product-description">{{ $product->productDescription }}</p>
            @endif

            @if($product->productStock > 0)
            @php $maxQty = min($product->productMaxQuantity, $product->productStock); @endphp

            @if($maxQty > 1)
            <div class="qty-wrap">
                <label style="font-size:13px; color:#555; font-weight:600;">Quantity</label>
                <input type="number" id="qty-main" class="qty-input" min="1" max="{{ $maxQty }}" value="1">
                <span style="font-size:13px; color:#aaa;">max {{ $maxQty }}</span>
            </div>
            @endif

            <div style="display:flex; flex-direction:column; gap:8px;">
                <form id="form-buynow" method="POST" action="/store/{{ $product->productSlug }}/checkout">
                    @csrf
                    <input type="hidden" name="quantity" id="qty-buynow" value="1">
                    <button type="submit" class="btn-buynow">
                        <i class="fa-brands fa-stripe-s"></i>
                        Buy now — ${{ number_format($product->productPrice, 2) }}
                    </button>
                </form>

                <form id="form-addtocart" method="POST" action="/store/{{ $product->productSlug }}/add-to-cart">
                    @csrf
                    <input type="hidden" name="quantity" id="qty-addtocart" value="1">
                    <button type="submit" class="btn-addtocart">
                        <i class="fa-solid fa-cart-plus"></i> Add to cart
                    </button>
                </form>
            </div>

            <p style="font-size:12px; color:#aaa; text-align:center; margin-top:-4px;">
                Secure checkout via Stripe
            </p>
            @else
            <button disabled style="width:100%; padding:14px 24px; background:#e8e8e8; color:#aaa; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:not-allowed;">
                Out of stock
            </button>
            @endif

            <a href="/store" style="color:#888; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to store
            </a>
        </div>
    </div>
</div>

@if($product->productStock > 0 && min($product->productMaxQuantity, $product->productStock) > 1)
@push('scripts')
<script>
    const qtyMain     = document.getElementById('qty-main');
    const qtyBuynow   = document.getElementById('qty-buynow');
    const qtyAddtocart = document.getElementById('qty-addtocart');
    qtyMain.addEventListener('input', function () {
        qtyBuynow.value    = this.value;
        qtyAddtocart.value = this.value;
    });
</script>
@endpush
@endif

@endsection
