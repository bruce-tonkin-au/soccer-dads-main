@extends('layouts.app')
@section('title', 'Store — Soccer Dads')

@push('styles')
<style>
    .store-hero {
        background: #262c39;
        padding: 4rem 2rem;
        text-align: center;
    }
    .store-hero h1 {
        font-family: 'GetShow', sans-serif;
        font-size: 48px;
        font-weight: normal;
        color: #fff;
        margin-bottom: 0.75rem;
    }
    .store-hero p {
        color: rgba(255,255,255,0.6);
        font-size: 16px;
    }
    .store-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1.5rem;
        padding: 3rem 0;
    }
    .product-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 16px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        transition: box-shadow 0.2s, transform 0.2s;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .product-card img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        display: block;
    }
    .product-card-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .product-card-name {
        font-size: 16px;
        font-weight: 600;
        color: #262c39;
    }
    .product-card-price {
        font-size: 20px;
        font-weight: 700;
        color: #888;
    }
    .product-card-stock {
        font-size: 12px;
        color: #aaa;
        margin-top: auto;
        padding-top: 8px;
    }
    .badge-outofstock {
        display: inline-block;
        background: #f4f4f4;
        color: #aaa;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    @media (max-width: 600px) {
        .store-grid { grid-template-columns: 1fr 1fr; gap: 1rem; }
        .store-hero h1 { font-size: 32px; }
    }
</style>
@endpush

@section('content')

<div class="store-hero">
    <h1>Store</h1>
    <p>Official gear for players &amp; fans</p>
</div>

<div style="background:#f8f8f8; padding-bottom:4rem;">
    <div class="container">
        @if($products->isEmpty())
        <div style="text-align:center; padding:6rem 0; color:#aaa;">
            <i class="fa-solid fa-bag-shopping" style="font-size:48px; margin-bottom:1rem; display:block;"></i>
            <p style="font-size:18px;">No products available right now.</p>
        </div>
        @else
        <div class="store-grid">
            @foreach($products as $product)
            <a href="/store/{{ $product->productSlug }}" class="product-card">
                <img src="{{ $product->productImage }}" alt="{{ $product->productName }}">
                <div class="product-card-body">
                    <div class="product-card-name">{{ $product->productName }}</div>
                    <div class="product-card-price">${{ number_format($product->productPrice, 2) }}</div>
                    @if($product->productStock <= 0)
                    <div class="product-card-stock"><span class="badge-outofstock">Out of stock</span></div>
                    @else
                    <div class="product-card-stock">{{ $product->productStock }} in stock</div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection
