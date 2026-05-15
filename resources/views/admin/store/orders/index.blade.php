@extends('admin.layout')
@section('title', 'Store — Orders')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
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
        white-space: nowrap;
    }
    .status-pending  { background:#fff8e6; color:#d4a017; }
    .status-paid     { background:#f0fdf4; color:#7bba56; }
    .status-shipped  { background:#e8f4ff; color:#458bc8; }
    .status-complete { background:#f0fdf4; color:#3a8c3f; }
    .payment-pending { background:#fff8e6; color:#d4a017; }
    .payment-paid    { background:#f0fdf4; color:#7bba56; }
    .order-items-list { font-size: 13px; color: #555; line-height: 1.6; }

    /* DataTables theme overrides */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
        font-size: 14px;
        color: #888;
    }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
        background: #fff;
        margin-left: 6px;
    }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #458bc8; }
    .dataTables_wrapper .dataTables_info { font-size: 12px; color: #aaa; margin-top: 0.75rem; }
    .dataTables_wrapper .dataTables_paginate { margin-top: 0.75rem; font-size: 14px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        color: #262c39 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #f4f4f4; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #262c39 !important; color: #fff !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color: #ccc !important; cursor: default; }
    table.dataTable thead th { cursor: pointer; }
    table.dataTable thead th.dt-orderable-asc,
    table.dataTable thead th.dt-orderable-desc { cursor: pointer; }
</style>
@endpush

@section('content')

<div class="store-tabs">
    <a href="/admin/store/products" class="store-tab">Products</a>
    <a href="/admin/store/orders" class="store-tab active">Orders</a>
</div>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Orders ({{ $orders->count() }})</h2>
    </div>

    @if($orders->isEmpty())
    <p style="color:#aaa; text-align:center; padding:3rem 0;">No orders yet.</p>
    @else
    <table id="orders-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            @php
                $customerSort = trim($order->memberName) ?: ($order->orderName ?? '');
            @endphp
            <tr>
                <td style="color:#aaa; font-size:13px; white-space:nowrap;">#{{ $order->orderID }}</td>
                <td style="font-weight:500; white-space:nowrap;" data-order="{{ $customerSort }}">
                    @if(trim($order->memberName))
                        {{ trim($order->memberName) }}
                    @elseif($order->orderName)
                        {{ $order->orderName }}
                        <div style="font-size:11px; color:#aaa;">Guest</div>
                    @else
                        <span style="color:#aaa;">Guest</span>
                    @endif
                </td>
                <td>
                    <div class="order-items-list">
                        @forelse($order->items as $item)
                        <div>{{ $item->itemQuantity }} &times; {{ $item->productName }}</div>
                        @empty
                        <span style="color:#ccc;">—</span>
                        @endforelse
                    </div>
                </td>
                <td style="color:#aaa; font-size:13px; white-space:nowrap;"
                    data-order="{{ \Carbon\Carbon::parse($order->created_at)->timestamp }}">
                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                </td>
                <td style="font-weight:600; white-space:nowrap;"
                    data-order="{{ $order->orderTotal }}">
                    ${{ number_format($order->orderTotal, 2) }}
                </td>
                <td>
                    @if($order->orderStatus === 'pending')
                    <span class="status-badge payment-pending">Pending</span>
                    @else
                    <span class="status-badge payment-paid">Paid</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $order->orderStatus }}">{{ ucfirst($order->orderStatus) }}</span>
                </td>
                <td>
                    <a href="/admin/store/orders/{{ $order->orderID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px; white-space:nowrap;">
                        <i class="fa-solid fa-pen"></i> Manage
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@push('scripts')
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#orders-table', {
            order: [[3, 'desc']], // Date descending by default
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [2, 7] } // Items and Actions not sortable
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ orders',
                infoEmpty: 'No orders found',
                infoFiltered: '(filtered from _MAX_ total)',
                zeroRecords: 'No matching orders found',
                paginate: { previous: '‹', next: '›' }
            }
        });
    });
</script>
@endpush

@endsection
