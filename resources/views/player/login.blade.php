<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Soccer Dads</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'GetShow';
            src: url('/fonts/get_show.woff2') format('woff2'),
                 url('/fonts/get_show.woff') format('woff');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #262c39;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-wrap {
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .login-logo { width: 80px; margin: 0 auto 1rem; }
        .login-title {
            font-family: 'GetShow';
            font-size: 56px;
            color: #fff;
            margin-bottom: 0.5rem;
            font-weight: normal;
        }
        .login-subtitle {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 2rem;
        }
        .login-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 2rem;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.5);
            margin-bottom: 8px;
            text-align: left;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #fff;
            outline: none;
            margin-bottom: 1rem;
        }
        .form-input:focus {
            border-color: rgba(255,255,255,0.5);
        }
        .btn-login {
            width: 100%;
            background: #fff;
            color: #262c39;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-login:hover { background: #f0f0f0; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 1rem;
            text-align: left;
        }
        .alert-error { background: rgba(226,75,74,0.2); border: 1px solid rgba(226,75,74,0.4); color: #fff; }
        .alert-success { background: rgba(123,186,86,0.2); border: 1px solid rgba(123,186,86,0.4); color: #fff; }
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
        }
        .back-link:hover { color: rgba(255,255,255,0.7); }
    </style>
</head>
<body>
    <div class="login-wrap">
        <img src="/images/Soccer-Dads-Logo.png" class="login-logo" alt="Soccer Dads">
        <h1 class="login-title">Player Login</h1>
        <p class="login-subtitle">Enter your 3-character player code to receive a login link.</p>

        @if(session('error'))
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @if(session('success'))
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif

        @if(session('no_email'))
        <div class="alert alert-error">
            <i class="fa-solid fa-envelope"></i>
            Hi {{ session('member_name') }}! We don't have an email address on file for you.
            Please message Bruce to get this added before you can log in.
        </div>
        @endif

        <div class="login-card">
            <form method="POST" action="/login">
                @csrf
                <label class="form-label">Your player code</label>
                <input type="text" name="code" class="form-input" placeholder="···" maxlength="3" autocomplete="off" autocapitalize="characters" autofocus>
                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-paper-plane"></i> Send login link
                </button>
            </form>
        </div>

        <a href="/" class="back-link"><i class="fa-solid fa-chevron-left"></i> Back to Soccer Dads</a>
    </div>
</body>
</html>
