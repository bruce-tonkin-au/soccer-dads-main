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
            background: #262c39;
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
            white-space: nowrap;
            flex-shrink: 0;
        }
        .navbar-brand img {
            height: 36px;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            white-space: nowrap;
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
        /* About dropdown */
        .nav-dropdown {
            position: relative;
        }
        .nav-dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-caret {
            display: inline-flex;
            font-size: 10px;
            transition: transform 0.2s;
        }
        .nav-dropdown-menu {
            display: none;
            list-style: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 190px;
            background: #262c39;
            padding: 8px 0;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            z-index: 101;
        }
        .nav-dropdown-menu li { width: 100%; }
        .nav-dropdown-menu a {
            display: block;
            padding: 10px 20px;
            font-size: 14px;
            white-space: nowrap;
        }
        @media (min-width: 992px) {
            .nav-dropdown:hover .nav-dropdown-menu { display: block; }
            .nav-dropdown:hover .nav-caret { transform: rotate(180deg); }
        }
        .nav-cart-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .nav-cart-badge {
            position: absolute;
            top: -7px;
            right: -10px;
            background: #e24b4a;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
            pointer-events: none;
        }
        .nav-cart-label { display: none; }

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

        /* Nav: switch cleanly from full nav to burger at the lg breakpoint so
           the items never wrap and the Login button never orphans. */
        @media (max-width: 991px) {
    #burger { display:block !important; }
    .navbar-nav {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 64px;
        left: 0;
        width: 100%;
        background: #262c39;
        padding: 1rem 0;
        z-index: 99;
        gap: 0;
    }
    .navbar-nav.open {
        display: flex !important;
    }
    .navbar-nav li {
        width: 100%;
    }
    .navbar-nav a {
        display: block;
        padding: 12px 2rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        font-size: 15px;
    }
    .navbar-nav .btn-nav {
        margin: 1rem 2rem;
        display: block;
        text-align: center;
        border-radius: 8px;
    }
    .nav-cart-label { display: inline; margin-left: 6px; }

    /* About dropdown on mobile: inline expand on tap */
    .nav-dropdown-toggle { justify-content: space-between; }
    .nav-caret {
        padding: 12px;
        margin: -12px -1rem -12px 0;
    }
    .nav-dropdown-menu {
        position: static;
        min-width: 0;
        background: rgba(255,255,255,0.04);
        padding: 0;
        border-radius: 0;
        box-shadow: none;
    }
    .nav-dropdown.open .nav-dropdown-menu { display: block; }
    .nav-dropdown.open .nav-caret { transform: rotate(180deg); }
    .nav-dropdown-menu a { padding-left: 3rem; }
}
        @media (max-width: 768px) {
    #footer-grid {
        grid-template-columns: 1fr 1fr !important;
    }
    #footer-grid > div:nth-child(1),
    #footer-grid > div:nth-child(3) {
        display: none;
    }
    #footer-bottom {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
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
    <button id="burger" onclick="toggleMenu()" style="display:none; background:none; border:none; cursor:pointer; color:#fff; font-size:22px; padding:8px;">
        <i class="fa-solid fa-bars" id="burger-icon"></i>
    </button>
    <ul class="navbar-nav" id="navbar-nav">
        <li><a href="/">Home</a></li>
        <li><a href="/seasons">Seasons</a></li>
        <li><a href="/players">Players</a></li>
        <li class="nav-dropdown">
            <a href="/about" class="nav-dropdown-toggle">
                About
                <span class="nav-caret" onclick="toggleDropdown(event, this)"><i class="fa-solid fa-chevron-down"></i></span>
            </a>
            <ul class="nav-dropdown-menu">
                <li><a href="/about/history">History</a></li>
                <li><a href="/about/locations">Locations</a></li>
                <li><a href="/about/honour-board">Honour Board</a></li>
            </ul>
        </li>
        <li><a href="/store">Store</a></li>
        <li><a href="/contact">Contact</a></li>
        @php $cartCount = array_sum(array_column(session('store_cart', []), 'quantity')); @endphp
        <li>
            <a href="/store/cart">
                <span class="nav-cart-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                    @if($cartCount > 0)
                    <span class="nav-cart-badge">{{ $cartCount }}</span>
                    @endif
                </span>
                <span class="nav-cart-label">Cart</span>
            </a>
        </li>
        @if(session('player_id'))
            @php $navPlayer = DB::table('members')->where('memberID', session('player_id'))->value('memberNameFirst'); @endphp
            <li><a href="/portal" class="btn-nav"><i class="fa-solid fa-user"></i> {{ $navPlayer }}</a></li>
        @else
            <li><a href="/login" class="btn-nav"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
        @endif
    </ul>
</nav>

<main style="background:#fff;">
    @yield('content')
</main>

<footer style="background:#262c39; padding:4rem 2rem 2rem;">
    <div class="container">
        <div id="footer-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:3rem; margin-bottom:3rem;">

            {{-- Brand --}}
            <div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:1rem;">
                    <img src="/images/Soccer-Dads-Logo.png" style="width:48px;">
                    <span style="font-family:'GetShow'; font-size:32px; color:#fff; font-weight:normal;">Soccer Dads</span>
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:rgba(255,255,255,0.4); margin-bottom:1rem;">Quick links</p>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li><a href="/" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-house" style="width:16px;"></i> Home</a></li>
                    <li><a href="/seasons" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-chart-line" style="width:16px;"></i> Seasons</a></li>
                    <li><a href="/players" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-users" style="width:16px;"></i> Players</a></li>
                    <li><a href="/about" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-circle-info" style="width:16px;"></i> About</a></li>
                    <li><a href="/contact" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-envelope" style="width:16px;"></i> Contact</a></li>
                </ul>
            </div>

            {{-- Connect --}}
            <div>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:rgba(255,255,255,0.4); margin-bottom:1rem;">Connect</p>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li><a href="https://www.facebook.com/SoccerDads/" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-brands fa-facebook" style="width:16px;"></i> Facebook</a></li>
                    <li><a href="https://twitter.com/soccdads/" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-brands fa-x-twitter" style="width:16px;"></i> X</a></li>
                    <li><a href="https://instagram.com/soccdads/" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-brands fa-instagram" style="width:16px;"></i> Instagram</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:rgba(255,255,255,0.4); margin-bottom:1rem;">Contact</p>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li><a href="tel:0428400013" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-phone" style="width:16px;"></i> 0428 400 013</a></li>
                    <li><a href="mailto:admin@soccerdads.com.au" style="color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-envelope" style="width:16px;"></i> admin@</a></li>
                </ul>
            </div>

        </div>

        <div id="footer-bottom" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:1.5rem; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; color:rgba(255,255,255,0.3);">© {{ date('Y') }} Soccer Dads</span>
            <span style="font-size:13px; color:rgba(255,255,255,0.3);">Adelaide Hills, South Australia</span>
        </div>
    </div>
</footer>

@stack('scripts')

<script>
    function toggleMenu() {
        const nav = document.getElementById('navbar-nav');
        const icon = document.getElementById('burger-icon');
        nav.classList.toggle('open');
        icon.className = nav.classList.contains('open') ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    }
    // Mobile: tap the caret to expand the About sub-menu without following the /about link.
    function toggleDropdown(event, el) {
        event.preventDefault();
        event.stopPropagation();
        el.closest('.nav-dropdown').classList.toggle('open');
    }
</script>

</body>
</html>