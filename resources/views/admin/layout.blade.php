<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Soccer Dads</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f4; }
        .admin-nav {
            background: #262c39;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            height: 56px;
            gap: 2rem;
        }
        .admin-nav-brand { color: #fff; font-weight: 700; font-size: 16px; text-decoration: none; }
        .admin-nav a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 14px; }
        .admin-nav a:hover { color: #fff; }
        .admin-container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .admin-card { background: #fff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid #e8e8e8; }
        .admin-card h2 { font-size: 18px; font-weight: 600; color: #262c39; margin-bottom: 1rem; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; }
        .btn-primary { background: #262c39; color: #fff; }
        .btn-primary:hover { background: #1a1f2a; }
        .btn-secondary { background: #f4f4f4; color: #262c39; border: 1px solid #e8e8e8; }
        .btn-secondary:hover { background: #e8e8e8; }
        .btn-danger { background: #e24b4a; color: #fff; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-bottom: 6px; }
        .form-control { width: 100%; border: 1px solid #e8e8e8; border-radius: 8px; padding: 10px 14px; font-size: 15px; color: #262c39; outline: none; }
        .form-control:focus { border-color: #458bc8; }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 1rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #7bba56; color: #262c39; }
        .alert-error { background: #fff3f3; border: 1px solid #e24b4a; color: #262c39; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead th { background: #f8f8f8; padding: 10px 16px; text-align: left; font-weight: 600; color: #262c39; border-bottom: 1px solid #e8e8e8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; color: #262c39; }
        tbody tr:hover td { background: #f8f8f8; }
    </style>
    @stack('styles')
</head>
<body>

<nav class="admin-nav">
    <a href="/admin" class="admin-nav-brand">⚽ Soccer Dads Admin</a>
    <a href="/admin/players">Players</a>
    <a href="/admin/seasons">Seasons</a>
    <a href="/admin/ratings">Ratings</a>
    <a href="/admin/messages">Messages</a>
    <form method="POST" action="/admin/logout" style="margin-left:auto;">
        @csrf
        <button type="submit" style="background:none; border:none; color:rgba(255,255,255,0.6); cursor:pointer; font-size:14px;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </form>
</nav>

<div class="admin-container">
    @if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>
