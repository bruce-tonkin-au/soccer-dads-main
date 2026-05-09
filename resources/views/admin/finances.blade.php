@extends('admin.layout')
@section('title', 'Finances')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
    <h1 style="font-size:24px; font-weight:700; color:#262c39;">Finances</h1>
</div>

<div class="admin-card">
    <form method="GET" action="/admin/finances" style="margin-bottom:1.25rem;">
        <div style="display:flex; gap:8px; max-width:400px;">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search by player name…"
                class="form-control"
                style="flex:1;"
                autofocus
            >
            @if($search)
            <a href="/admin/finances" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    @if($transactions->isEmpty())
    <p style="color:#888; font-size:14px;">No transactions found{{ $search ? ' matching "' . e($search) . '"' : '' }}.</p>
    @else
    <div style="font-size:12px; color:#aaa; margin-bottom:8px;">{{ $transactions->count() }} transaction{{ $transactions->count() === 1 ? '' : 's' }}{{ $search ? ' matching "' . e($search) . '"' : '' }}</div>
    <table>
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
            @endphp
            <tr>
                <td>
                    <a href="/admin/players/{{ $t->memberID }}/edit" style="color:#458bc8; text-decoration:none;">
                        {{ $t->memberNameFirst }} {{ $t->memberNameLast }}
                    </a>
                </td>
                <td style="white-space:nowrap; color:#888;">
                    {{ \Carbon\Carbon::parse($t->accountCreated)->format('j M Y') }}
                </td>
                <td style="text-align:right; font-weight:600; font-variant-numeric:tabular-nums; white-space:nowrap; color:{{ $isDeposit ? '#7bba56' : '#262c39' }};">
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
    @endif
</div>

@push('scripts')
<script>
(function () {
    var input = document.querySelector('input[name="search"]');
    if (!input) return;
    var timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            input.closest('form').submit();
        }, 400);
    });
}());
</script>
@endpush

@endsection
