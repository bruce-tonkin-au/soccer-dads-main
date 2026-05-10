@extends('layouts.app')
@section('title', 'Order confirmed — Soccer Dads Store')

@push('styles')
<style>
    .confirm-wrap {
        max-width: 560px;
        margin: 0 auto;
        padding: 4rem 2rem;
        text-align: center;
    }
    .confirm-icon {
        width: 72px;
        height: 72px;
        background: #f4f4f4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 32px;
        color: #888;
    }
    .confirm-heading {
        font-size: 28px;
        font-weight: 700;
        color: #262c39;
        margin-bottom: 0.5rem;
    }
    .confirm-sub {
        color: #888;
        font-size: 15px;
        margin-bottom: 2rem;
    }
    .order-summary {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: left;
        margin-bottom: 2rem;
    }
    .order-summary-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #aaa;
        margin-bottom: 1rem;
    }
    .order-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 15px;
        color: #262c39;
    }
    .order-row + .order-row {
        border-top: 1px solid #f0f0f0;
    }
    .order-row-label { color: #555; }
    .order-row-value { font-weight: 600; }
    .order-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid #e8e8e8;
        font-size: 17px;
        font-weight: 700;
        color: #262c39;
    }
</style>
@endpush

@section('content')

<div style="background:#f8f8f8; min-height:80vh; padding-bottom:4rem;">
    <div class="confirm-wrap">

        <div class="confirm-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <h1 class="confirm-heading">Order confirmed!</h1>
        <p class="confirm-sub">
            Thanks for your purchase. We'll be in touch soon to arrange delivery.
        </p>

        <div class="order-summary">
            <div class="order-summary-label">Order summary</div>

            @foreach($items as $item)
            <div class="order-row">
                <span class="order-row-label">
                    {{ $item->productName }}
                    @if($item->itemQuantity > 1)
                    <span style="color:#aaa;"> × {{ $item->itemQuantity }}</span>
                    @endif
                </span>
                <span class="order-row-value">${{ number_format($item->itemPrice * $item->itemQuantity, 2) }}</span>
            </div>
            @endforeach

            @if($order)
            <div class="order-row">
                <span class="order-row-label">Order #</span>
                <span style="color:#aaa; font-size:13px;">#{{ $order->orderID }}</span>
            </div>
            <div class="order-row">
                <span class="order-row-label">Status</span>
                <span style="background:#f4f4f4; color:#888; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:700;">Paid</span>
            </div>
            @endif

            <div class="order-total-row">
                <span>Total paid</span>
                <span>${{ number_format($session->amount_total / 100, 2) }} AUD</span>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px;">
            <a href="/store" style="display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 24px; background:#262c39; color:#fff; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none;">
                <i class="fa-solid fa-bag-shopping"></i> Back to store
            </a>
            @if(session('player_id'))
            <a href="/portal" style="display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 24px; background:#f4f4f4; color:#262c39; border-radius:10px; font-size:15px; font-weight:600; text-decoration:none;">
                <i class="fa-solid fa-user"></i> My portal
            </a>
            @endif
        </div>

        <p style="font-size:13px; color:#bbb; margin-top:2rem;">
            Questions? Email <a href="mailto:admin@soccerdads.com.au" style="color:#888;">admin@soccerdads.com.au</a>
        </p>

    </div>
</div>

@endsection
