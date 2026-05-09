@extends('admin.layout')
@section('title', 'Store — Orders')

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

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Orders ({{ $orders->count() }})</h2>
    </div>

    @if($orders->isEmpty())
    <p style="color:#aaa; text-align:center; padding:3rem 0;">No orders yet.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Player</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td style="color:#aaa; font-size:13px;">#{{ $order->orderID }}</td>
                <td style="font-weight:500;">
                    {{ trim($order->memberName) ?: 'Guest' }}
                </td>
                <td style="font-weight:600;">${{ number_format($order->orderTotal, 2) }}</td>
                <td>
                    <span class="status-badge status-{{ $order->orderStatus }}">{{ $order->orderStatus }}</span>
                </td>
                <td style="color:#aaa; font-size:13px;">
                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                </td>
                <td>
                    <a href="/admin/store/orders/{{ $order->orderID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Manage
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
