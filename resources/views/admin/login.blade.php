<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Soccer Dads</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, sans-serif; background: #262c39; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: #fff; border-radius: 16px; padding: 2rem; width: 100%; max-width: 380px; }
        h1 { font-size: 20px; font-weight: 700; color: #262c39; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 12px; font-weight: 600; color: #888; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
        input { width: 100%; border: 1px solid #e8e8e8; border-radius: 8px; padding: 10px 14px; font-size: 15px; outline: none; }
        input:focus { border-color: #458bc8; }
        button { width: 100%; background: #262c39; color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
        .error { background: #fff3f3; border: 1px solid #e24b4a; border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #e24b4a; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>⚽ Admin Login</h1>
        @if(session('error'))
        <div class="error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="/admin/login">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
            </div>
            <button type="submit">Log in</button>
        </form>
    </div>
</body>
</html>
