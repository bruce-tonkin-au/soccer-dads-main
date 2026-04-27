@extends('admin.layout')
@section('title', 'Edit Player')
@section('content')

<div class="admin-card" style="max-width:600px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/players" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit — {{ $player->memberNameFirst }} {{ $player->memberNameLast }}</h2>
    </div>

    <div style="background:#f8f8f8; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:13px; color:#888;">
        Member code: <strong style="color:#262c39;">{{ $player->memberCode }}</strong> &nbsp;·&nbsp;
        Member key: <strong style="color:#262c39;">{{ $player->memberKey }}</strong> &nbsp;·&nbsp;
        Slug: <strong style="color:#262c39;">{{ $player->memberSlug }}</strong>
    </div>

    <form method="POST" action="/admin/players/{{ $player->memberID }}/edit">
        @csrf
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">First name</label>
                <input type="text" name="firstName" class="form-control" value="{{ $player->memberNameFirst }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last name</label>
                <input type="text" name="lastName" class="form-control" value="{{ $player->memberNameLast }}" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" value="{{ $player->memberEmail }}">
        </div>
        <div class="form-group">
            <label class="form-label">Mobile number</label>
            <input type="tel" name="mobile" class="form-control" value="{{ $player->memberPhoneMobile }}">
        </div>
        <div class="form-group">
            <label class="form-label">Parent member (if child)</label>
            <select name="parent" class="form-control">
                <option value="">None</option>
                @foreach($allPlayers as $p)
                @if($p->memberID != $player->memberID)
                <option value="{{ $p->memberID }}" {{ $player->memberParent == $p->memberID ? 'selected' : '' }}>
                    {{ $p->memberNameFirst }} {{ $p->memberNameLast }}
                </option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="active" class="form-control">
                <option value="1" {{ $player->memberActive ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$player->memberActive ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Birthday</label>
            <input type="date" name="birthday" class="form-control" value="{{ $player->memberBirthday ? \Carbon\Carbon::parse($player->memberBirthday)->format('Y-m-d') : '' }}">
        </div>
        <div style="display:flex; gap:8px; margin-top:1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save changes
            </button>
            <a href="/players/{{ $player->memberSlug }}" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View profile
            </a>
        </div>
    </form>
</div>

@endsection
