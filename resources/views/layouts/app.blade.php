<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Soccer Dads')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'GetShow';
            src: url('/fonts/get_show.woff2') format('woff2'),
                 url('/fonts/get_show.woff') format('woff');
            font-weight: normal;
            font-style: normal;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #262c39;
            background: #fff;
        }

        /* Navigation */
        .navbar {
            background: #262c39;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-brand {
            font-family: 'GetShow';
            font-weight: normal;
            font-size: 28px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-brand img {
            height: 36px;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }
        .navbar-nav a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .navbar-nav a:hover {
            color: #fff;
        }
        .navbar-nav .btn-nav {
            background: #fff;
            color: #262c39;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .navbar-nav .btn-nav:hover {
            background: #f0f0f0;
            color: #262c39;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #262c39;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1a1f2a;
        }
        .btn-secondary {
            background: #f4f4f4;
            color: #262c39;
        }
        .btn-secondary:hover {
            background: #e8e8e8;
        }
        .btn-white {
            background: #fff;
            color: #262c39;
        }
        .btn-white:hover {
            background: #f0f0f0;
        }

        /* Footer */
        .footer {
            background: #262c39;
            color: rgba(255,255,255,0.6);
            padding: 3rem 2rem;
            margin-top: 4rem;
        }
        .footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
        }
        .footer-brand {
            font-family: 'GetShow';
            font-size: 32px;
            color: #fff;
            margin-bottom: 1rem;
        }
        .footer-tagline {
            font-size: 14px;
            line-height: 1.6;
        }
        .footer-heading {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.4);
            margin-bottom: 1rem;
        }
        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .footer-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 14px;
        }
        .footer-links a:hover {
            color: #fff;
        }
        .footer-bottom {
            max-width: 1100px;
            margin: 2rem auto 0;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 13px;
            display: flex;
            justify-content: space-between;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem;
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">
        <img src="/images/Soccer-Dads-Logo.png" alt="Soccer Dads">
        Soccer Dads
    </a>
    <ul class="navbar-nav">
        <li><a href="/seasons">Seasons</a></li>
        <li><a href="/players">Players</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/login" class="btn-nav">Login</a></li>
    </ul>
</nav>

@yield('content')

<footer class="footer">
    <div class="footer-inner">
        <div>
            <div class="footer-brand">Soccer Dads</div>
            <p class="footer-tagline">Established 2011</p>
        </div>
        <div>
            <p class="footer-heading">Quick links</p>
            <ul class="footer-links">
                <li><a href="/">Home</a></li>
                <li><a href="/seasons">Seasons</a></li>
                <li><a href="/players">Players</a></li>
                <li><a href="/about">About</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-heading">Connect</p>
            <ul class="footer-links">
                <li><a href="https://www.facebook.com/SoccerDads/"><i class="fa-brands fa-facebook"></i> Facebook</a></li>
                <li><a href="https://twitter.com/soccdads/"><i class="fa-brands fa-twitter"></i> Twitter</a></li>
                <li><a href="https://instagram.com/soccdads/"><i class="fa-brands fa-instagram"></i> Instagram</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© {{ date('Y') }} Soccer Dads</span>
        <span>Developed by Codesnap</span>
    </div>
</footer>

@stack('scripts')
</body>
</html>