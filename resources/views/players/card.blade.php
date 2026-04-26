<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $member->memberNameFirst }} {{ $member->memberNameLast }} — Soccer Dads Card</title>
    <meta property="og:title" content="{{ $member->memberNameFirst }} {{ $member->memberNameLast }} — Soccer Dads">
    <meta property="og:description" content="Overall {{ $overall }} · {{ $position }} · Soccer Dads">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'GetShow';
            src: url('/fonts/get_show.woff2') format('woff2'),
                 url('/fonts/get_show.woff') format('woff');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .card-wrap {
            position: relative;
            width: 300px;
            height: 420px;
            border-radius: 16px;
            background: linear-gradient(160deg, {{ $team['card'] }}, #1a1a2e);
            box-shadow: 0 0 40px rgba(0,0,0,0.6), inset 0 0 60px rgba(255,255,255,0.05);
            padding: 20px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .card-rating {
            font-size: 52px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .card-position {
            font-size: 16px;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 4px;
        }

        .card-flag {
            margin-left: auto;
            font-size: 28px;
        }

        .card-photo {
            width: 180px;
            height: 180px;
            margin: 0 auto 8px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-photo-placeholder {
            font-size: 80px;
            color: rgba(255,255,255,0.3);
        }

        .card-name {
            font-family: 'GetShow';
            font-weight: normal;
            font-size: 26px;
            color: #fff;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
            margin-bottom: 12px;
            line-height: 1;
        }

        .card-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 12px;
        }

        .card-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
        }

        .card-stat {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-stat-value {
            font-size: 20px;
            font-weight: 900;
            color: #fff;
            min-width: 28px;
        }

        .card-stat-label {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .card-footer {
            margin-top: auto;
            text-align: center;
        }

        .card-logo {
            width: 32px;
            opacity: 0.6;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 2rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover { color: rgba(255,255,255,0.8); }

        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 1.5rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        .share-btn:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<div class="card-wrap" id="player-card">
    <div class="card-top">
        <div>
            <div class="card-rating">{{ $overall }}</div>
            <div class="card-position">{{ $position }}</div>
        </div>
        <div class="card-flag">⚽</div>
    </div>

    <div class="card-photo">
        @if($member->memberPhotoCard)
            <img src="{{ Storage::url($member->memberPhotoCard) }}" alt="{{ $member->memberNameFirst }}">
        @else
            <i class="fa-solid fa-user card-photo-placeholder"></i>
        @endif
    </div>

    <div class="card-name">{{ $member->memberNameFirst }} {{ $member->memberNameLast }}</div>

    <hr class="card-divider">

    <div class="card-stats">
        <div class="card-stat">
            <span class="card-stat-value">{{ $pace }}</span>
            <span class="card-stat-label">PAC</span>
        </div>
        <div class="card-stat">
            <span class="card-stat-value">{{ $defending }}</span>
            <span class="card-stat-label">DEF</span>
        </div>
        <div class="card-stat">
            <span class="card-stat-value">{{ $shooting }}</span>
            <span class="card-stat-label">SHO</span>
        </div>
        <div class="card-stat">
            <span class="card-stat-value">{{ $physical }}</span>
            <span class="card-stat-label">PHY</span>
        </div>
        <div class="card-stat">
            <span class="card-stat-value">{{ $passing }}</span>
            <span class="card-stat-label">PAS</span>
        </div>
        <div class="card-stat">
            <span class="card-stat-value">{{ min(99, $awards * 10) }}</span>
            <span class="card-stat-label">AWD</span>
        </div>
    </div>

    <div class="card-footer">
        <img src="/images/Soccer-Dads-Logo.png" class="card-logo" alt="Soccer Dads">
    </div>
</div>

<button class="share-btn" onclick="copyLink()">
    <i class="fa-solid fa-share-nodes"></i> Share card
</button>

<a href="/players/{{ $member->memberSlug }}" class="back-link">
    <i class="fa-solid fa-chevron-left"></i> Back to profile
</a>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.querySelector('.share-btn');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Link copied!';
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-share-nodes"></i> Share card';
        }, 2000);
    });
}
</script>

</body>
</html>
