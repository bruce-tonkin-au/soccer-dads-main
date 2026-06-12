@extends('admin.layout')
@section('title', 'Transfer')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-control { padding: 0; }
    .ts-control {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 15px;
        color: #262c39;
    }
    .ts-control:focus, .ts-wrapper.focus .ts-control { border-color: #458bc8; box-shadow: none; }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
    <a href="/admin/finances" class="btn btn-secondary" style="padding:6px 12px;">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
    <h1 style="font-size:24px; font-weight:700; color:#262c39;">Transfer between members</h1>
</div>

<div class="admin-card" style="max-width:560px;">
    @if($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/admin/finances/transfer">
        @csrf

        <div class="form-group">
            <label class="form-label">From member (debited)</label>
            <select name="fromMemberID" class="form-control searchable" required>
                <option value="">Select a member…</option>
                @foreach($members as $m)
                <option value="{{ $m->memberID }}" {{ old('fromMemberID') == $m->memberID ? 'selected' : '' }}>
                    {{ $m->memberNameFirst }} {{ $m->memberNameLast }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">To member (credited)</label>
            <select name="toMemberID" class="form-control searchable" required>
                <option value="">Select a member…</option>
                @foreach($members as $m)
                <option value="{{ $m->memberID }}" {{ old('toMemberID') == $m->memberID ? 'selected' : '' }}>
                    {{ $m->memberNameFirst }} {{ $m->memberNameLast }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Amount</label>
            <input type="number" name="amount" step="0.01" min="0.01" class="form-control"
                   placeholder="25.00" value="{{ old('amount') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" maxlength="255"
                   placeholder="e.g. Hyder father-son transfer" value="{{ old('description') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control"
                   value="{{ old('date', now()->format('Y-m-d')) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-right-left"></i> Complete transfer
        </button>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.querySelectorAll('select.searchable').forEach(function (el) {
        new TomSelect(el, { create: false, allowEmptyOption: true });
    });
</script>
@endpush

@endsection
