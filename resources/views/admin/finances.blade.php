@extends('admin.layout')
@section('title', 'Finances')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    /* Override DataTables chrome to match admin theme */
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
        padding: 8px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
        background: #fff;
        margin-left: 6px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #458bc8;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 12px;
        color: #aaa;
        padding-top: 14px;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 5px 11px !important;
        border-radius: 6px !important;
        font-size: 13px !important;
        color: #262c39 !important;
        border: 1px solid #e8e8e8 !important;
        margin: 0 2px !important;
        box-shadow: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        color: #262c39 !important;
        border-color: #ccc !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #262c39 !important;
        color: #fff !important;
        border-color: #262c39 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #ccc !important;
        border-color: #e8e8e8 !important;
        background: none !important;
        cursor: default;
    }

    /* Keep existing table styles; override DataTables table resets */
    table.dataTable {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 0 !important;
    }
    table.dataTable thead th {
        background: #f8f8f8 !important;
        padding: 10px 16px !important;
        text-align: left !important;
        font-weight: 600 !important;
        color: #262c39 !important;
        border-bottom: 1px solid #e8e8e8 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-top: none !important;
    }
    table.dataTable thead th.dt-right { text-align: right !important; }
    table.dataTable tbody td {
        padding: 12px 16px !important;
        border-bottom: 1px solid #f0f0f0 !important;
        color: #262c39 !important;
        border-top: none !important;
    }
    table.dataTable tbody tr:hover td { background: #f8f8f8; }
    table.dataTable thead .sorting,
    table.dataTable thead .sorting_asc,
    table.dataTable thead .sorting_desc { background-color: #f8f8f8 !important; }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
    <h1 style="font-size:24px; font-weight:700; color:#262c39;">Finances</h1>
    <div style="display:flex; gap:8px;">
        <a href="/admin/finances/transaction" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Transaction
        </a>
        <a href="/admin/finances/transfer" class="btn btn-secondary">
            <i class="fa-solid fa-right-left"></i> Transfer
        </a>
    </div>
</div>

<div class="admin-card">
    <table id="transactions-table">
        <thead>
            <tr>
                <th>Player</th>
                <th>Date</th>
                <th style="text-align:right;">Amount</th>
                <th>Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            @php
                $isDeposit = $t->accountValue > 0;
                $amount    = abs($t->accountValue);

                if ($t->accountComment) {
                    $description = $t->accountComment;
                } elseif ($isDeposit && $t->paymentSource) {
                    $description = ucfirst($t->paymentSource);
                } elseif ($isDeposit) {
                    $description = 'Payment';
                } elseif ($t->gameID) {
                    $description = 'Game #' . $t->gameID;
                } else {
                    $description = '—';
                }

                if (str_starts_with($description, 'Stripe top-up') ||
                    str_starts_with($description, 'cs_live_') ||
                    str_starts_with($description, 'cs_test_')) {
                    $description = 'Account top-up';
                }
            @endphp
            <tr>
                <td>
                    <a href="/admin/players/{{ $t->memberID }}/edit" style="color:#458bc8; text-decoration:none;">
                        {{ $t->memberNameFirst }} {{ $t->memberNameLast }}
                    </a>
                </td>
                <td data-order="{{ \Carbon\Carbon::parse($t->accountCreated)->format('Ymd') }}" style="white-space:nowrap; color:#888;">
                    {{ \Carbon\Carbon::parse($t->accountCreated)->format('j M Y') }}
                </td>
                <td data-order="{{ $t->accountValue }}" style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums; white-space:nowrap; color:{{ $isDeposit ? '#7bba56' : '#262c39' }};">
                    {{ $isDeposit ? '+' : '−' }}${{ number_format($amount, 2) }}
                </td>
                <td>
                    @if($isDeposit)
                    <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; background:#f0fdf4; color:#7bba56;">Deposit</span>
                    @else
                    <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; background:#f4f4f4; color:#888;">Charge</span>
                    @endif
                </td>
                <td style="color:#888;">{{ $description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(function () {
    $('#transactions-table').DataTable({
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [
            { targets: 2, className: 'dt-right' }
        ],
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ transactions',
            infoEmpty: 'No transactions found',
            infoFiltered: '(filtered from _MAX_ total)',
            zeroRecords: 'No matching transactions found',
            paginate: { previous: '‹', next: '›' }
        }
    });
});
</script>
@endpush

@endsection
