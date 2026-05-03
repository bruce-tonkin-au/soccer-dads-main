<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, sans-serif; background: #f8f8f8; padding: 2rem; }
        .card { background: #fff; border-radius: 12px; padding: 2rem; max-width: 500px; margin: 0 auto; }
        .logo { font-size: 24px; font-weight: 700; color: #262c39; margin-bottom: 1rem; }
        .btn { display: inline-block; background: #262c39; color: #fff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 1.5rem 0; }
        .note { font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">⚽ Soccer Dads</div>
        <h2 style="color:#262c39; margin-bottom:0.5rem;">Great news, {{ $name }}!</h2>
        <p style="color:#444; line-height:1.6;">A spot has opened up and you've been automatically moved from the reserves bench to the active player list. You're in for the next game!</p>
        <a href="{{ $link }}" class="btn">View your registration</a>
        <p class="note">If you can no longer make it, please update your registration so someone else can take the spot.</p>
    </div>
</body>
</html>
