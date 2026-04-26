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
        <h2 style="color:#262c39; margin-bottom:0.5rem;">Hi {{ $member->memberNameFirst }}!</h2>
        <p style="color:#444; line-height:1.6;">Click the button below to log in to your Soccer Dads account. This link is valid for 30 minutes.</p>
        <a href="{{ $loginUrl }}" class="btn">Log in to Soccer Dads</a>
        <p class="note">If you didn't request this, you can safely ignore this email. The link will expire on its own.</p>
        <p class="note" style="margin-top:1rem;">Or copy this link: {{ $loginUrl }}</p>
    </div>
</body>
</html>
