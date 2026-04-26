<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Done — Soccer Dads</title>
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
            text-align: center;
        }
        .wrap { max-width: 400px; }
        h1 {
            font-family: 'GetShow';
            font-size: 48px;
            color: #fff;
            font-weight: normal;
            margin-bottom: 1rem;
        }
        p { font-size: 16px; color: rgba(255,255,255,0.6); line-height: 1.6; margin-bottom: 2rem; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #262c39;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <i class="fa-solid fa-trophy" style="font-size:64px; color:#f0c040; margin-bottom:1.5rem; display:block;"></i>
        <h1>You're a legend, {{ $rater->memberNameFirst }}!</h1>
        <p>
            You've rated {{ $totalRated }} players.
            Your ratings help us build better teams and make games more competitive.
        </p>
        <p style="margin-bottom:2rem;">Come back after the next game to rate any new players you've played with.</p>
        <a href="/" class="btn">
            <i class="fa-solid fa-house"></i> Back to Soccer Dads
        </a>
    </div>
</body>
</html>
