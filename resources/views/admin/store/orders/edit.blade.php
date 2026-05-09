@extends('admin.layout')
@section('title', 'Order #' . $order->orderID)

@push('styles')
<style>
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
</style>
@endpush

@section('content')

<div style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/store/orders" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Order #{{ $order->orderID }}</h2>
        <span class="status-badge status-{{ $order->orderStatus }}" style="font-size:14px; padding:4px 14px;">{{ $order->orderStatus }}</span>
    </div>

    <div class="admin-card">
        <h2>Order details</h2>
        <table style="margin-bottom:0;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->productName }}</td>
                    <td>{{ $item->itemQuantity }}</td>
                    <td>${{ number_format($item->itemPrice, 2) }}</td>
                    <td style="font-weight:600;">${{ number_format($item->itemPrice * $item->itemQuantity, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:600; padding-top:16px; border-top:2px solid #e8e8e8;">Order total</td>
                    <td style="font-weight:700; font-size:16px; padding-top:16px; border-top:2px solid #e8e8e8;">${{ number_format($order->orderTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if($items->isEmpty())
        <p style="color:#aaa; padding:1rem 0;">No items recorded for this order.</p>
        @endif
    </div>

    <div class="admin-card">
        <h2>Customer</h2>
        <p style="font-size:14px; color:#555;">
            {{ trim($order->memberName) ?: 'Guest order (no member linked)' }}
        </p>
        @if($order->memberID)
        <a href="/admin/players/{{ $order->memberID }}/edit" class="btn btn-secondary" style="margin-top:8px; padding:6px 14px; font-size:13px;">
            <i class="fa-solid fa-user"></i> View player
        </a>
        @endif
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
</div>

@endsection
