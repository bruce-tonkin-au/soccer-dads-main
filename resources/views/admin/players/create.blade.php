@extends('admin.layout')
@section('title', 'Add Player')
@section('content')

<div class="admin-card" style="max-width:600px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/players" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Add new player</h2>
    </div>

    <form method="POST" action="/admin/players/create">
        @csrf
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">First name</label>
                <input type="text" name="firstName" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Last name</label>
                <input type="text" name="lastName" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Mobile number</label>
            <input type="tel" name="mobile" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Parent member (if child)</label>
            <input type="number" name="parent" class="form-control" placeholder="Parent memberID (optional)">
        </div>
        <div style="margin-top:1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-user-plus"></i> Create player
            </button>
        </div>
    </form>
</div>

@endsection
