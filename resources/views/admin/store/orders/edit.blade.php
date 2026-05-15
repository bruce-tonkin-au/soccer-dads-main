@extends('admin.layout')
@section('title', 'Order #' . $order->orderID)

@push('styles')
<style>
    .store-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e8e8e8;
        padding-bottom: 0;
    }
    .store-tab {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #888;
        text-decoration: none;
        border-radius: 8px 8px 0 0;
        margin-bottom: -2px;
        border-bottom: 2px solid transparent;
    }
    .store-tab:hover { color: #262c39; }
    .store-tab.active { color: #262c39; border-bottom-color: #262c39; }
    .status-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-pending  { background:#fff8e6; color:#d4a017; }
    .status-paid     { background:#f0fdf4; color:#7bba56; }
    .status-shipped  { background:#e8f4ff; color:#458bc8; }
    .status-complete { background:#f0fdf4; color:#3a8c3f; }
    .detail-row { display:flex; gap:1rem; margin-bottom:0.6rem; font-size:14px; }
    .detail-label { color:#888; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; min-width:160px; padding-top:2px; }
    .detail-value { color:#262c39; }
    .stripe-ref { font-family: monospace; font-size: 13px; background: #f8f8f8; padding: 4px 10px; border-radius: 6px; border: 1px solid #e8e8e8; word-break: break-all; }
</style>
@endpush

@section('content')

<div class="store-tabs">
    <a href="/admin/store/products" class="store-tab">Products</a>
    <a href="/admin/store/orders" class="store-tab active">Orders</a>
</div>

<div style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/store/orders" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Order #{{ $order->orderID }}</h2>
        <span class="status-badge status-{{ $order->orderStatus }}" style="font-size:14px; padding:4px 14px;">{{ ucfirst($order->orderStatus) }}</span>
    </div>

    <div class="admin-card">
        <h2>Line items</h2>
        @if($items->isEmpty())
        <p style="color:#aaa; padding:0.5rem 0;">No items recorded for this order.</p>
        @else
        <table style="margin-bottom:0;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Unit price</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->productName }}</td>
                    <td style="text-align:center;">{{ $item->itemQuantity }}</td>
                    <td style="text-align:right;">${{ number_format($item->itemPrice, 2) }}</td>
                    <td style="text-align:right; font-weight:600;">${{ number_format($item->itemPrice * $item->itemQuantity, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:600; padding-top:16px; border-top:2px solid #e8e8e8;">Order total</td>
                    <td style="text-align:right; font-weight:700; font-size:16px; padding-top:16px; border-top:2px solid #e8e8e8;">${{ number_format($order->orderTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <div class="admin-card">
        <h2>Customer</h2>
        @if($order->memberID && trim($order->memberName))
        <div class="detail-row">
            <div class="detail-label">Name</div>
            <div class="detail-value">{{ trim($order->memberName) }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Member ID</div>
            <div class="detail-value">#{{ $order->memberID }}</div>
        </div>
        <a href="/admin/players/{{ $order->memberID }}/edit" class="btn btn-secondary" style="margin-top:8px; padding:6px 14px; font-size:13px;">
            <i class="fa-solid fa-user"></i> View player
        </a>
        @elseif($order->orderName || $order->orderEmail)
        <div class="detail-row">
            <div class="detail-label">Name</div>
            <div class="detail-value">{{ $order->orderName ?: '—' }}</div>
        </div>
        @if($order->orderEmail)
        <div class="detail-row">
            <div class="detail-label">Email</div>
            <div class="detail-value">
                <a href="mailto:{{ $order->orderEmail }}" style="color:#458bc8;">{{ $order->orderEmail }}</a>
            </div>
        </div>
        @endif
        @if($order->orderPhone)
        <div class="detail-row">
            <div class="detail-label">Phone</div>
            <div class="detail-value">{{ $order->orderPhone }}</div>
        </div>
        @endif
        <p style="font-size:12px; color:#aaa; margin-top:8px;">Guest order — not linked to a member account.</p>
        @else
        <p style="font-size:14px; color:#aaa;">Guest order — no customer details recorded.</p>
        @endif
    </div>

    <div class="admin-card">
        <h2>Payment</h2>
        <div class="detail-row">
            <div class="detail-label">Payment status</div>
            <div class="detail-value">
                @if($order->orderStatus === 'pending')
                <span style="background:#fff8e6; color:#d4a017; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Pending</span>
                @else
                <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Paid</span>
                @endif
            </div>
        </div>
        @if($order->stripeSessionID)
        <div class="detail-row">
            <div class="detail-label">Stripe session</div>
            <div class="detail-value">
                <span class="stripe-ref">{{ $order->stripeSessionID }}</span>
            </div>
        </div>
        @endif
        <div class="detail-row" style="margin-top:0.4rem;">
            <div class="detail-label">Order date</div>
            <div class="detail-value">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, g:ia') }}</div>
        </div>
    </div>

    <div class="admin-card">
        <h2>Update order</h2>
        <form method="POST" action="/admin/store/orders/{{ $order->orderID }}/edit">
            @csrf
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="orderStatus" class="form-control" style="max-width:240px;">
                    @foreach(['pending','paid','shipped','complete'] as $status)
                    <option value="{{ $status }}" {{ $order->orderStatus === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="orderNotes" class="form-control" rows="3" style="resize:vertical;">{{ $order->orderNotes }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save changes
            </button>
        </form>
    </div>

    <div class="admin-card" style="border-color:#fde8e8;">
        <h2 style="color:#e24b4a;">Danger zone</h2>
        <p style="font-size:14px; color:#888; margin-bottom:1rem;">Permanently delete this order and all its line items. This cannot be undone.</p>
        <form method="POST" action="/admin/store/orders/{{ $order->orderID }}/delete"
              onsubmit="return confirm('Delete order #{{ $order->orderID }} and all its line items? This cannot be undone.')">
            @csrf
            <button type="submit" class="btn btn-danger" style="padding:8px 16px; font-size:13px;">
                <i class="fa-solid fa-trash"></i> Delete order
            </button>
        </form>
    </div>
</div>

@endsection
